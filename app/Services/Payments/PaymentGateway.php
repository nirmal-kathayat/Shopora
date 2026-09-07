<?php

namespace App\Services\Payments;

use App\Models\Sales;

/**
 * One way of taking money for a storefront order.
 *
 * Every gateway works the same shape: we hand the customer over to it, they
 * pay somewhere we do not control, and they come back through a callback we
 * have to verify before we believe a word of it. What differs is only how the
 * handover looks (a signed form to POST, a session URL to follow) and how the
 * gateway proves a payment happened - so that is all this interface pins down,
 * plus the one question every gateway must be able to answer on demand:
 * "was this order actually paid for?"
 *
 * The callbacks themselves stay in each gateway's own controller. They are too
 * different to pretend otherwise - eSewa signs a payload into the return URL,
 * Stripe posts a webhook - and flattening them into one method would only
 * hide the difference behind a cast.
 */
interface PaymentGateway
{
    /** The gateway says the payment went through, for the amount we expected. */
    public const STATUS_COMPLETE = 'complete';

    /** The gateway says it did not (pending, cancelled, not found, wrong amount). */
    public const STATUS_INCOMPLETE = 'incomplete';

    /** We could not reach the gateway to ask - neither a yes nor a no. */
    public const STATUS_UNKNOWN = 'unknown';

    /** What goes in sales.payment_method, and what the storefront asks for by name. */
    public function key(): string;

    /** The payment_modes title its sales are booked under. */
    public function title(): string;

    /**
     * Whether this gateway has credentials to work with. An unconfigured one is
     * hidden from the storefront rather than offered and then failing.
     */
    public function configured(): bool;

    /** Everything the browser needs to leave the site and pay. */
    public function checkout(CheckoutRequest $request): Handoff;

    /**
     * Ask the gateway itself whether this order was paid - the authoritative
     * check, independent of whatever the customer's browser did or did not
     * tell us. Tri-state on purpose: a definite "no" must be allowed to cancel
     * an order, but an unreachable gateway must never lose a paid one.
     *
     * @param  string  $totalAmount  What we expect to have been charged, formatted by PaymentSettlement::amount().
     * @return array{state: string, reference: ?string, reported: ?string}
     */
    public function fetchStatus(Sales $order, string $totalAmount): array;
}
