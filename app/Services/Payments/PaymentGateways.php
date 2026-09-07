<?php

namespace App\Services\Payments;

use App\Models\Sales;

/**
 * The gateways this shop can take money through, by name.
 *
 * Everything that has to deal with more than one gateway - the storefront
 * asking what it may offer, a callback working out who is calling, the
 * reconciler asking each order's own gateway about it - comes through here, so
 * adding a third is a matter of registering it and nothing else.
 */
final class PaymentGateways
{
    /** @var array<string, PaymentGateway> */
    private readonly array $gateways;

    public function __construct(PaymentGateway ...$gateways)
    {
        $keyed = [];
        foreach ($gateways as $gateway) {
            $keyed[$gateway->key()] = $gateway;
        }

        $this->gateways = $keyed;
    }

    /** The named gateway, if it exists and has credentials to work with. */
    public function find(string $key): ?PaymentGateway
    {
        $gateway = $this->gateways[$key] ?? null;

        return $gateway?->configured() ? $gateway : null;
    }

    /**
     * The gateway an order was placed through. Unlike find(), this does not
     * care whether it is still configured: a payment already in flight has to
     * be finished off whatever the shop has since turned off.
     */
    public function for(Sales $order): ?PaymentGateway
    {
        return $this->gateways[$order->payment_method] ?? null;
    }

    /** @return list<PaymentGateway> Everything a customer may actually be sent to. */
    public function available(): array
    {
        return array_values(array_filter(
            $this->gateways,
            fn (PaymentGateway $gateway) => $gateway->configured()
        ));
    }
}
