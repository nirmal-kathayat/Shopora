<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use App\Repository\OrderRepository;
use App\Services\EsewaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Online payment for a storefront order. Right now that means eSewa (ePay v2).
 *
 * The order is created up front in 'pending_payment' - reserving its stock but
 * counting for nothing in the reports - and only becomes a real, 'placed' order
 * once eSewa confirms the money. The customer leaves the site to pay and comes
 * back through a signed callback, so the two ends of the flow live here: one
 * authenticated call to start it, and two public redirects to finish it.
 *
 * eSewa has no server-to-server webhook: the only word we get is carried by the
 * customer's own browser, which may never arrive. Everything a returning
 * browser tells us is verified, and everything it fails to tell us is picked up
 * later by payments:reconcile.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly EsewaPaymentService $esewa,
    ) {
    }

    /**
     * Start an eSewa payment. Creates the pending order and hands the browser
     * back everything it needs to POST eSewa's form - action URL and fields,
     * signature included.
     */
    public function initiateEsewa(Request $request): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $address = $customer->addresses()->find($data['address_id']);
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Choose a delivery address first.',
            ]);
        }

        // A fresh attempt supersedes any earlier one left hanging, so stock is
        // never quietly tied up by an abandoned checkout.
        $this->orders->cancelPendingPayments($customer);

        $result = $this->orders->place($customer, $address, 'esewa', 'pending_payment');
        $order = $result['order'];

        $uuid = $order->id . '-' . strtoupper(Str::random(6));
        $order->update(['payment_uuid' => $uuid]);

        $amount = $this->money($result['subtotal']);
        $delivery = $this->money($result['deliveryFee']);
        $total = $this->money($result['total']);

        $fields = $this->esewa->formFields(
            $uuid,
            $amount,
            $delivery,
            $total,
            route('payment.esewa.success'),
            route('payment.esewa.failure', [
                'oid' => $uuid,
                // Our own token, so only a browser coming back through the link
                // we handed eSewa can cancel this order.
                'sig' => $this->esewa->callbackToken($uuid),
            ]),
        );

        $this->log('initiated', $uuid, ['order_id' => $order->id, 'total' => $total]);

        return response()->json([
            'action' => $this->esewa->formUrl(),
            'fields' => $fields,
            'order' => ['id' => $order->id, 'code' => $order->code],
        ]);
    }

    /**
     * eSewa sends the browser here with a signed ?data= payload once the
     * customer has paid. We trust it only after the signature checks out and
     * eSewa's own server confirms the transaction, then flip the order to a
     * real, paid order and empty the cart.
     */
    public function esewaSuccess(Request $request): RedirectResponse
    {
        $payload = $this->esewa->verifyCallback($request->query('data'));

        if (! $payload) {
            $this->log('callback rejected: bad signature or payload', null, [
                'data' => (string) $request->query('data'),
            ]);

            return $this->redirectFront('/checkout?payment=failed');
        }

        $uuid = (string) $payload['transaction_uuid'];
        $order = Sales::storefront()->where('payment_uuid', $uuid)->first();

        if (! $order) {
            $this->log('callback rejected: no such order', $uuid);

            return $this->redirectFront('/checkout?payment=failed');
        }

        // Already settled - a refreshed callback, or one that raced another.
        if ($order->payment_status === 'paid') {
            $this->log('callback replayed, already paid', $uuid, ['order_id' => $order->id]);

            return $this->redirectFront('/account?section=orders&payment=success');
        }

        $total = $this->money($this->orders->total($order));
        $amountOk = $this->esewa->amountsMatch((string) $payload['total_amount'], $total);

        // A definite "no" from eSewa blocks it; an unreachable status API does
        // not, because the signed callback already vouched for the payment.
        // Asked outside any transaction - a lock must not wait on the network.
        $status = $this->esewa->checkStatus($uuid, $total);
        $rejected = $status === EsewaPaymentService::STATUS_INCOMPLETE;

        if (! $amountOk || $rejected) {
            $cancelled = $this->orders->failPending($order);

            $this->log('callback rejected', $uuid, [
                'order_id' => $order->id,
                'reason' => ! $amountOk ? 'amount mismatch' : 'gateway says incomplete',
                'claimed' => (string) $payload['total_amount'],
                'expected' => $total,
                'cancelled' => $cancelled,
            ]);

            return $this->redirectFront('/checkout?payment=failed');
        }

        $settled = $this->orders->settlePaid($order, $payload['transaction_code'] ?? null);

        $this->log($settled ? 'paid' : 'already settled by another call', $uuid, [
            'order_id' => $order->id,
            'total' => $total,
            'gateway_status' => $status,
            'ref' => $payload['transaction_code'] ?? null,
        ]);

        return $this->redirectFront('/account?section=orders&payment=success');
    }

    /**
     * eSewa sends the browser here when the customer backs out or the payment
     * fails. The pending order is cancelled and its units go back.
     *
     * There is no payload of eSewa's to verify here, so the link carries a
     * token of our own: without it, knowing a uuid would be enough to cancel
     * someone else's order.
     */
    public function esewaFailure(Request $request): RedirectResponse
    {
        $uuid = (string) $request->query('oid');

        if (! $this->esewa->callbackTokenMatches($uuid, $request->query('sig'))) {
            $this->log('failure callback rejected: bad token', $uuid, [
                'ip' => $request->ip(),
            ]);

            return $this->redirectFront('/checkout?payment=failed');
        }

        $order = Sales::storefront()->where('payment_uuid', $uuid)->first();

        if ($order && $this->orders->failPending($order)) {
            $this->log('cancelled by customer at gateway', $uuid, ['order_id' => $order->id]);
        }

        return $this->redirectFront('/checkout?payment=failed');
    }

    /** eSewa compares amounts verbatim, so keep them plain: no separators, no trailing zeros. */
    private function money(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function redirectFront(string $path): RedirectResponse
    {
        return redirect()->away(rtrim(config('services.frontend.url'), '/') . $path);
    }

    /** One line per payment decision, kept for disputes. @see config/logging.php */
    private function log(string $event, ?string $uuid, array $context = []): void
    {
        Log::channel('payment')->info('esewa: ' . $event, ['uuid' => $uuid] + $context);
    }
}
