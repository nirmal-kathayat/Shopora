<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Sales;
use App\Repository\OrderRepository;
use App\Services\EsewaPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            route('payment.esewa.failure', ['oid' => $uuid]),
        );

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
            return $this->redirectFront('/checkout?payment=failed');
        }

        $order = Sales::storefront()->where('payment_uuid', $payload['transaction_uuid'])->first();
        if (! $order) {
            return $this->redirectFront('/checkout?payment=failed');
        }

        // Already settled (a refreshed callback, say) - just take them onward.
        if ($order->payment_status === 'paid') {
            return $this->redirectFront('/account?section=orders&payment=success');
        }

        $total = $this->money((float) $this->orderTotal($order));

        $amountOk = $this->esewa->amountsMatch((string) $payload['total_amount'], $total);
        // A definite "no" from eSewa blocks it; an unreachable status API does
        // not, because the signed callback already vouched for the payment.
        $status = $this->esewa->checkStatus($payload['transaction_uuid'], $total);
        $rejected = $status === EsewaPaymentService::STATUS_INCOMPLETE;

        if (! $amountOk || $rejected) {
            DB::transaction(function () use ($order) {
                $this->orders->releaseStock($order);
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            });

            return $this->redirectFront('/checkout?payment=failed');
        }

        DB::transaction(function () use ($order, $payload) {
            $order->update([
                'status' => 'placed',
                'payment_status' => 'paid',
                'payment_ref' => $payload['transaction_code'] ?? null,
            ]);

            CartItem::where('customer_id', $order->customer_id)->delete();
        });

        return $this->redirectFront('/account?section=orders&payment=success');
    }

    /**
     * eSewa sends the browser here when the customer backs out or the payment
     * fails. The pending order is cancelled and its units go back.
     */
    public function esewaFailure(Request $request): RedirectResponse
    {
        $order = Sales::storefront()
            ->where('payment_uuid', $request->query('oid'))
            ->where('status', 'pending_payment')
            ->first();

        if ($order) {
            DB::transaction(function () use ($order) {
                $this->orders->releaseStock($order);
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            });
        }

        return $this->redirectFront('/checkout?payment=failed');
    }

    /** The order's own total: line items plus its delivery fee. */
    private function orderTotal(Sales $order): float
    {
        $lines = DB::table('sales_products')
            ->where('sales_id', $order->id)
            ->selectRaw('COALESCE(SUM(qty * price_per_unit), 0) as total')
            ->value('total');

        return (float) $lines + (float) $order->delivery_fee;
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
}
