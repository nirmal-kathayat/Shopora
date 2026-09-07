<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ReturnsToStorefront;
use App\Http\Controllers\Controller;
use App\Models\Sales;
use App\Services\Payments\PaymentSettlement;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Where a Stripe payment comes back to.
 *
 * Two ways, and they are not equals. The webhook is the one that decides: it
 * comes from Stripe, is signed, and arrives whether or not the customer's
 * browser survived the trip. The return URL is only there so a customer who
 * does come straight back sees their paid order immediately instead of a
 * pending one - it settles the same order, from the same question put to
 * Stripe, and if it never runs nothing is lost.
 *
 * Both end in settlePaid(), which locks the row and does nothing the second
 * time, so the two racing each other is normal rather than a problem.
 */
class StripePaymentController extends Controller
{
    use ReturnsToStorefront;

    /** Session events worth acting on; everything else is acknowledged and dropped. */
    private const HANDLED = [
        'checkout.session.completed',
        'checkout.session.async_payment_succeeded',
        'checkout.session.async_payment_failed',
        'checkout.session.expired',
    ];

    public function __construct(
        private readonly StripePaymentService $stripe,
        private readonly PaymentSettlement $settlement,
    ) {
    }

    /**
     * Stripe sends the browser here after a payment attempt, with the session
     * id in the query string.
     *
     * That id arrives from the customer, so it proves nothing on its own - it
     * is used only to ask Stripe, with our own key, what happened. An id for
     * somebody else's session is harmless: it will not match an order of ours.
     */
    public function complete(Request $request): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id');

        if ($sessionId === '') {
            return $this->paymentFailed();
        }

        $session = $this->stripe->fetchSession($sessionId);

        if (! $session) {
            // Stripe was unreachable, or the id was invented. Either way the
            // reconciler will get to the order; do not cancel it on a guess.
            $this->settlement->log('stripe', 'return could not be checked with Stripe', null, [
                'session' => $sessionId,
            ]);

            return $this->paymentPending();
        }

        $uuid = $session['client_reference_id'] ?? null;
        $order = $this->settlement->find($uuid);

        if (! $order || $order->payment_session !== $sessionId) {
            $this->settlement->log('stripe', 'return rejected: no such order', $uuid, [
                'session' => $sessionId,
            ]);

            return $this->paymentFailed();
        }

        if ($order->payment_status === 'paid') {
            return $this->paymentSucceeded();
        }

        if (! $this->settle($order, $session, 'return')) {
            // Not paid: the customer got as far as Stripe's page and left, or
            // chose a method that settles later. The webhook has the last word
            // either way, so nothing is cancelled here.
            return $this->paymentPending();
        }

        return $this->paymentSucceeded();
    }

    /**
     * Stripe sends the browser here when the customer backs out of the payment
     * page. Nothing of Stripe's comes with it, so the link carries a token of
     * our own - without it, knowing a uuid would be enough to cancel a
     * stranger's order while they were still paying for it.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $uuid = (string) $request->query('oid');

        if (! $this->stripe->callbackTokenMatches($uuid, $request->query('sig'))) {
            $this->settlement->log('stripe', 'cancel callback rejected: bad token', $uuid, [
                'ip' => $request->ip(),
            ]);

            return $this->paymentFailed();
        }

        $order = $this->settlement->find($uuid);

        if ($order && $this->settlement->fail($order)) {
            $this->settlement->log('stripe', 'cancelled by customer at gateway', $uuid, ['order_id' => $order->id]);
        }

        return $this->paymentFailed();
    }

    /**
     * Stripe's own word on what happened, signed and delivered behind the
     * customer's back. This is what makes a Stripe order safe in a way an eSewa
     * one cannot be: closing the tab on the way back costs nothing.
     *
     * Anything that verifies is acknowledged with a 200, even when we decide to
     * do nothing about it - a non-2xx tells Stripe to keep redelivering, and an
     * event we have no use for would then be retried for days.
     */
    public function webhook(Request $request): Response
    {
        $event = $this->stripe->verifyWebhook($request->getContent(), $request->header('Stripe-Signature'));

        if (! $event) {
            $this->settlement->log('stripe', 'webhook rejected: bad signature', null, ['ip' => $request->ip()]);

            return response('Invalid signature', 400);
        }

        $type = (string) $event['type'];

        if (! in_array($type, self::HANDLED, true)) {
            return response('', 200);
        }

        $session = $event['data']['object'] ?? [];
        $uuid = is_array($session) ? ($session['client_reference_id'] ?? null) : null;
        $order = $this->settlement->find($uuid);

        if (! $order) {
            $this->settlement->log('stripe', 'webhook ignored: no such order', $uuid, ['event' => $type]);

            return response('', 200);
        }

        if ($type === 'checkout.session.completed' || $type === 'checkout.session.async_payment_succeeded') {
            $this->settle($order, $session, 'webhook ' . $type);

            return response('', 200);
        }

        // The payment failed outright, or the page was left to expire. Either
        // way there is no money and the units should go back. failPending()
        // refuses to touch an order that has since been paid.
        $cancelled = $this->settlement->fail($order);

        $this->settlement->log('stripe', 'webhook ' . $type, $uuid, [
            'order_id' => $order->id,
            'cancelled' => $cancelled,
        ]);

        return response('', 200);
    }

    /**
     * Settle the order if - and only if - Stripe says this session is paid, for
     * the amount the order actually comes to. The amount check is not
     * ceremony: without it a session opened for one order could settle another.
     *
     * @return bool whether Stripe considers the session paid, however the settle went
     */
    private function settle(Sales $order, array $session, string $source): bool
    {
        $total = $this->settlement->total($order);

        if (! $this->stripe->sessionIsPaid($session, $total)) {
            $this->settlement->log('stripe', $source . ': not paid', $order->payment_uuid, [
                'order_id' => $order->id,
                'payment_status' => $session['payment_status'] ?? null,
                'claimed' => $session['amount_total'] ?? null,
                'expected' => $total,
            ]);

            return false;
        }

        $reference = $this->stripe->reference($session);
        $settled = $this->settlement->settle($order, $reference);

        $this->settlement->log('stripe', $settled ? 'paid' : 'already settled by another call', $order->payment_uuid, [
            'order_id' => $order->id,
            'total' => $total,
            'source' => $source,
            'ref' => $reference,
        ]);

        return true;
    }
}
