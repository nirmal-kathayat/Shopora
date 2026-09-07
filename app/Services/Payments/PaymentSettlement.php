<?php

namespace App\Services\Payments;

use App\Models\Sales;
use App\Repository\OrderRepository;
use Illuminate\Support\Facades\Log;

/**
 * The shop's half of a payment: find the order a gateway is talking about,
 * work out what it should have cost, settle or give up on it, and leave a line
 * in the payment log saying why.
 *
 * It exists because three quite different callers need exactly this and must
 * not disagree about any of it - the browser coming back from a gateway, a
 * webhook arriving behind its back, and the nightly reconciler. The decisions
 * that move stock and money stay in OrderRepository, which locks; this only
 * makes sure everyone asks the same questions in the same words.
 */
final class PaymentSettlement
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    /** The storefront order a gateway's reference belongs to, if any. */
    public function find(?string $uuid): ?Sales
    {
        if (! $uuid) {
            return null;
        }

        return Sales::storefront()->where('payment_uuid', $uuid)->first();
    }

    /** What this order should have been charged, in the form gateways are compared against. */
    public function total(Sales $order): string
    {
        return $this->amount($this->orders->total($order));
    }

    /**
     * Amounts are kept plain - no separators, no trailing zeros - because eSewa
     * compares the string it signed character by character, and because a
     * single format across the gateways is one fewer thing to get wrong.
     */
    public function amount(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /** @return bool true only for the call that actually settled it */
    public function settle(Sales $order, ?string $reference): bool
    {
        return $this->orders->settlePaid($order, $reference);
    }

    /** @return bool true only for the call that actually cancelled it */
    public function fail(Sales $order): bool
    {
        return $this->orders->failPending($order);
    }

    /** One line per payment decision, kept for disputes. @see config/logging.php */
    public function log(string $gateway, string $event, ?string $uuid, array $context = []): void
    {
        Log::channel('payment')->info($gateway . ': ' . $event, ['uuid' => $uuid] + $context);
    }
}
