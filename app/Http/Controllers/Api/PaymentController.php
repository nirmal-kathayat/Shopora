<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ReturnsToStorefront;
use App\Http\Controllers\Controller;
use App\Repository\OrderRepository;
use App\Services\Payments\CheckoutRequest;
use App\Services\Payments\EsewaPaymentService;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\PaymentGateways;
use App\Services\Payments\PaymentSettlement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Online payment for a storefront order.
 *
 * The order is created up front in 'pending_payment' - reserving its stock but
 * counting for nothing in the reports - and only becomes a real, 'placed' order
 * once a gateway confirms the money. The customer leaves the site to pay and
 * comes back through a callback, so the flow lives in two halves: one
 * authenticated call to start it, here, and a set of public callbacks to finish
 * it - eSewa's below, Stripe's in StripePaymentController.
 *
 * Which gateways exist is the registry's business, not this controller's: it
 * asks for one by name, hands it the order, and passes whatever handover it
 * gets back to the browser.
 */
class PaymentController extends Controller
{
    use ReturnsToStorefront;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly PaymentGateways $gateways,
        private readonly PaymentSettlement $settlement,
        private readonly EsewaPaymentService $esewa,
    ) {
    }

    /**
     * The online payment methods this shop can actually take right now.
     *
     * The storefront asks before it draws the checkout, so a gateway with no
     * credentials is never offered and then found to be broken. Cash on
     * delivery is not in here - it needs no gateway and is always available.
     */
    public function methods(): JsonResponse
    {
        return response()->json([
            'methods' => array_map(fn (PaymentGateway $gateway) => [
                'key' => $gateway->key(),
                'title' => $gateway->title(),
            ], $this->gateways->available()),
        ]);
    }

    /**
     * Start a payment. Creates the pending order and hands the browser back
     * everything it needs to leave for the gateway - a form to POST, or a URL
     * to follow, depending on which one was asked for.
     */
    public function initiate(Request $request, string $gateway): JsonResponse
    {
        $customer = $request->user();

        $data = $request->validate([
            'address_id' => ['required', 'integer'],
        ]);

        $paymentGateway = $this->gateways->find($gateway);
        if (! $paymentGateway) {
            throw ValidationException::withMessages([
                'payment' => 'That payment method is not available right now.',
            ]);
        }

        $address = $customer->addresses()->find($data['address_id']);
        if (! $address) {
            throw ValidationException::withMessages([
                'address_id' => 'Choose a delivery address first.',
            ]);
        }

        // A fresh attempt supersedes any earlier one left hanging, so stock is
        // never quietly tied up by an abandoned checkout.
        $this->orders->cancelPendingPayments($customer);

        $result = $this->orders->place($customer, $address, $paymentGateway->key(), 'pending_payment');
        $order = $result['order'];

        $uuid = $order->id . '-' . strtoupper(Str::random(6));
        $order->update(['payment_uuid' => $uuid]);

        $checkout = new CheckoutRequest(
            order: $order,
            uuid: $uuid,
            amount: $this->settlement->amount($result['subtotal']),
            delivery: $this->settlement->amount($result['deliveryFee']),
            total: $this->settlement->amount($result['total']),
            lines: $this->orders->lines($order),
        );

        try {
            $handoff = $paymentGateway->checkout($checkout);
        } catch (PaymentGatewayException $e) {
            // The customer never reached the gateway, so nothing is in flight
            // and the units should go straight back rather than wait out the
            // reconciler. The shop is not told: it was asked for nothing.
            $this->orders->failPending($order, tellShop: false);

            $this->settlement->log($paymentGateway->key(), 'could not be started', $uuid, [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'payment' => 'We could not open the payment page. Please try again in a moment.',
            ]);
        }

        // Some gateways mint their own id for the attempt. It is the only handle
        // we have on the payment if no callback ever arrives.
        if ($handoff->session) {
            $order->update(['payment_session' => $handoff->session]);
        }

        $this->settlement->log($paymentGateway->key(), 'initiated', $uuid, [
            'order_id' => $order->id,
            'total' => $checkout->total,
        ]);

        return response()->json($handoff->toArray() + [
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
            $this->settlement->log('esewa', 'callback rejected: bad signature or payload', null, [
                'data' => (string) $request->query('data'),
            ]);

            return $this->paymentFailed();
        }

        $uuid = (string) $payload['transaction_uuid'];
        $order = $this->settlement->find($uuid);

        if (! $order) {
            $this->settlement->log('esewa', 'callback rejected: no such order', $uuid);

            return $this->paymentFailed();
        }

        // Already settled - a refreshed callback, or one that raced another.
        if ($order->payment_status === 'paid') {
            $this->settlement->log('esewa', 'callback replayed, already paid', $uuid, ['order_id' => $order->id]);

            return $this->paymentSucceeded();
        }

        $total = $this->settlement->total($order);
        $amountOk = $this->esewa->amountsMatch((string) $payload['total_amount'], $total);

        // A definite "no" from eSewa blocks it; an unreachable status API does
        // not, because the signed callback already vouched for the payment.
        // Asked outside any transaction - a lock must not wait on the network.
        $status = $this->esewa->fetchStatus($order, $total)['state'];
        $rejected = $status === PaymentGateway::STATUS_INCOMPLETE;

        if (! $amountOk || $rejected) {
            $cancelled = $this->settlement->fail($order);

            $this->settlement->log('esewa', 'callback rejected', $uuid, [
                'order_id' => $order->id,
                'reason' => ! $amountOk ? 'amount mismatch' : 'gateway says incomplete',
                'claimed' => (string) $payload['total_amount'],
                'expected' => $total,
                'cancelled' => $cancelled,
            ]);

            return $this->paymentFailed();
        }

        $settled = $this->settlement->settle($order, $payload['transaction_code'] ?? null);

        $this->settlement->log('esewa', $settled ? 'paid' : 'already settled by another call', $uuid, [
            'order_id' => $order->id,
            'total' => $total,
            'gateway_status' => $status,
            'ref' => $payload['transaction_code'] ?? null,
        ]);

        return $this->paymentSucceeded();
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
            $this->settlement->log('esewa', 'cancel callback rejected: bad token', $uuid, [
                'ip' => $request->ip(),
            ]);

            return $this->paymentFailed();
        }

        $order = $this->settlement->find($uuid);

        if ($order && $this->settlement->fail($order)) {
            $this->settlement->log('esewa', 'cancelled by customer at gateway', $uuid, ['order_id' => $order->id]);
        }

        return $this->paymentFailed();
    }
}
