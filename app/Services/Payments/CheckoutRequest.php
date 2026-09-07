<?php

namespace App\Services\Payments;

use App\Models\Sales;

/**
 * What a gateway is told about the order it is being asked to charge for.
 *
 * The amounts are strings, already in the exact form we will compare against
 * later: a gateway that signs them must sign the same characters it sends, and
 * one that wants integer minor units can widen them itself. $lines is there for
 * gateways that show the customer an itemised page of their own; the ones that
 * only take a total ignore it.
 */
final class CheckoutRequest
{
    /**
     * @param  string  $uuid  Our id for this attempt - what the gateway hands back so we can find the order again.
     * @param  string  $amount  Subtotal, before delivery.
     * @param  string  $delivery  Delivery fee, '0' when it is free.
     * @param  string  $total  What the customer is actually charged.
     * @param  list<array{name: string, qty: int, unit_amount: float}>  $lines
     */
    public function __construct(
        public readonly Sales $order,
        public readonly string $uuid,
        public readonly string $amount,
        public readonly string $delivery,
        public readonly string $total,
        public readonly array $lines = [],
    ) {
    }
}
