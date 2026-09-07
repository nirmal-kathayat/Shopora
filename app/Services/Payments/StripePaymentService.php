<?php

namespace App\Services\Payments;

use App\Models\Sales;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Stripe, through hosted Checkout: we create a Checkout Session over the API
 * and send the browser to the page Stripe mints for it, so no card detail ever
 * touches this application.
 *
 * Unlike eSewa there is a real webhook, and that is what settles an order -
 * the customer's return from Stripe is only the fast path, and both are
 * verified against Stripe rather than believed. Every amount crossing this
 * boundary is in minor units (paisa for NPR), because that is the only form
 * Stripe accepts and the only one that cannot round.
 *
 * The REST API is called directly rather than through stripe-php: what we need
 * of it is two endpoints and one HMAC, the same shape the eSewa client already
 * has, and it keeps the whole payment layer fakeable with Http::fake().
 *
 * @see https://docs.stripe.com/api/checkout/sessions
 */
class StripePaymentService implements PaymentGateway
{
    use SignsCallbacks;

    /**
     * Stripe's currencies that have no minor unit at all - an amount in them is
     * a whole number of the currency itself, not of hundredths. NPR is not one
     * of these, but a shop that switches STRIPE_CURRENCY should not silently
     * start charging a hundred times too much.
     *
     * @see https://docs.stripe.com/currencies#zero-decimal
     */
    private const ZERO_DECIMAL = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga',
        'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    /**
     * How long a Checkout Session stays payable. Stripe's floor is 30 minutes
     * measured at the moment it reads the request, so asking for exactly 30
     * loses the race to its own network latency; the few minutes over are what
     * make it reliable. Comfortably inside payments:reconcile's 45, which is
     * when an unpaid order is given up on and its stock handed back.
     */
    private const SESSION_MINUTES = 35;

    /** How far out of date a webhook's timestamp may be before we call it a replay. */
    private const WEBHOOK_TOLERANCE = 300;

    public function __construct(
        private readonly string $secret,
        private readonly string $webhookSecret,
        private readonly string $currency,
        private readonly string $apiUrl,
        private readonly ?string $apiVersion = null,
    ) {
    }

    public static function fromConfig(): self
    {
        $c = config('services.stripe');

        return new self(
            (string) $c['secret'],
            (string) $c['webhook_secret'],
            strtolower((string) $c['currency']),
            rtrim((string) $c['api_url'], '/'),
            $c['api_version'] ?: null,
        );
    }

    public function key(): string
    {
        return 'stripe';
    }

    public function title(): string
    {
        return 'Stripe';
    }

    /**
     * Without a secret key there is no Stripe. The webhook secret is checked
     * too: a Stripe that can take money but has no verified way of telling us
     * it did would leave paid orders sitting in pending_payment until a
     * reconciliation run happened to notice.
     */
    public function configured(): bool
    {
        return $this->secret !== '' && $this->webhookSecret !== '';
    }

    /**
     * Create the Checkout Session and hand back its URL. The customer sees
     * their own order itemised on Stripe's page; the session carries our uuid
     * in client_reference_id, which is how every callback finds the order again.
     */
    public function checkout(CheckoutRequest $request): Handoff
    {
        $response = $this->client()
            // Same uuid, same session: a customer who double-clicks "Pay" is
            // sent back to the one payment page instead of opening a second.
            ->withHeaders(['Idempotency-Key' => 'checkout-' . $request->uuid])
            ->asForm()
            ->post($this->apiUrl . '/checkout/sessions', [
                'mode' => 'payment',
                'client_reference_id' => $request->uuid,
                'line_items' => $this->lineItems($request),
                'success_url' => route('payment.stripe.return') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.stripe.cancel', [
                    'oid' => $request->uuid,
                    'sig' => $this->callbackToken($request->uuid),
                ]),
                'expires_at' => now()->addMinutes(self::SESSION_MINUTES)->timestamp,
                'customer_email' => $request->order->customer?->email,
                'metadata' => [
                    'order_uuid' => $request->uuid,
                    'order_id' => (string) $request->order->id,
                ],
                // Repeated on the charge itself, so a payment can be traced
                // back to an order from the Stripe dashboard alone.
                'payment_intent_data' => [
                    'metadata' => ['order_uuid' => $request->uuid],
                ],
            ]);

        if (! $response->successful() || ! $response->json('url')) {
            throw new PaymentGatewayException(
                'Stripe would not open a payment page: ' . $response->json('error.message', 'unknown error')
            );
        }

        return Handoff::redirect($response->json('url'), $response->json('id'));
    }

    /**
     * Ask Stripe whether the session we opened for this order was paid. An
     * order that never got as far as a session cannot have been paid, so it is
     * a definite no rather than an unknown - otherwise it would sit pending for
     * ever, holding its stock.
     */
    public function fetchStatus(Sales $order, string $totalAmount): array
    {
        if (! $order->payment_session) {
            return ['state' => self::STATUS_INCOMPLETE, 'reference' => null, 'reported' => 'no session'];
        }

        $session = $this->fetchSession($order->payment_session);

        if ($session === null) {
            return ['state' => self::STATUS_UNKNOWN, 'reference' => null, 'reported' => null];
        }

        $reported = (string) ($session['payment_status'] ?? '');

        return [
            'state' => $this->sessionIsPaid($session, $totalAmount) ? self::STATUS_COMPLETE : self::STATUS_INCOMPLETE,
            'reference' => $this->reference($session),
            'reported' => $reported !== '' ? $reported : null,
        ];
    }

    /** One Checkout Session, straight from Stripe; null when we could not ask. */
    public function fetchSession(string $sessionId): ?array
    {
        try {
            $response = $this->client()->get($this->apiUrl . '/checkout/sessions/' . urlencode($sessionId));
        } catch (\Throwable $e) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Stripe says this session is paid, for the amount we expected. Both halves
     * matter: payment_status alone would let a session whose amount was tampered
     * with on the way to Stripe settle an order it never covered.
     */
    public function sessionIsPaid(array $session, string $totalAmount): bool
    {
        return ($session['payment_status'] ?? null) === 'paid'
            && (int) ($session['amount_total'] ?? -1) === $this->minorUnits($totalAmount);
    }

    /** The reference to keep for this payment: the charge, not the checkout page. */
    public function reference(array $session): ?string
    {
        $intent = $session['payment_intent'] ?? null;

        // Expanded or not, depending on how the object was fetched.
        if (is_array($intent)) {
            $intent = $intent['id'] ?? null;
        }

        return is_string($intent) && $intent !== '' ? $intent : null;
    }

    /**
     * Verify a webhook and return its event.
     *
     * Stripe signs the raw request body together with the timestamp it sent, so
     * the body has to be the untouched bytes - re-encoding the parsed JSON
     * would change them and every signature would fail. Returns null for
     * anything that does not check out, including a signature that is valid but
     * too old to be anything but a replay.
     *
     * @see https://docs.stripe.com/webhooks#verify-manually
     */
    public function verifyWebhook(string $payload, ?string $signatureHeader): ?array
    {
        if ($this->webhookSecret === '' || ! $signatureHeader) {
            return null;
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$name, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($name === 't') {
                $timestamp = $value;
            } elseif ($name === 'v1' && $value !== null) {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || ! ctype_digit($timestamp) || $signatures === []) {
            return null;
        }

        if (abs(time() - (int) $timestamp) > self::WEBHOOK_TOLERANCE) {
            return null;
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $this->webhookSecret);

        $matched = false;
        foreach ($signatures as $signature) {
            // Every candidate is checked, and none short-circuits the loop:
            // the work done here must not depend on which one matched.
            $matched = hash_equals($expected, $signature) || $matched;
        }

        if (! $matched) {
            return null;
        }

        $event = json_decode($payload, true);

        return is_array($event) && isset($event['type']) ? $event : null;
    }

    protected function callbackSecret(): string
    {
        return $this->secret;
    }

    /**
     * The order, as Stripe will show it to the customer: a line per product,
     * plus delivery when it is charged for.
     *
     * The lines have to add up to the total exactly - it is the total we later
     * check the payment against - so they are summed and, if anything has drifted,
     * dropped in favour of a single line for the whole order. Prices are stored
     * to two decimals, so that is a guard, not an expectation.
     */
    private function lineItems(CheckoutRequest $request): array
    {
        $items = [];

        foreach ($request->lines as $line) {
            $items[] = $this->lineItem($line['name'], $this->minorUnits((string) $line['unit_amount']), (int) $line['qty']);
        }

        $delivery = $this->minorUnits($request->delivery);
        if ($delivery > 0) {
            $items[] = $this->lineItem('Delivery', $delivery, 1);
        }

        $total = $this->minorUnits($request->total);
        $summed = array_sum(array_map(
            fn ($item) => $item['price_data']['unit_amount'] * $item['quantity'],
            $items
        ));

        if ($items === [] || $summed !== $total) {
            return [$this->lineItem('Order ' . $request->order->code, $total, 1)];
        }

        return $items;
    }

    private function lineItem(string $name, int $unitAmount, int $quantity): array
    {
        return [
            'quantity' => max(1, $quantity),
            'price_data' => [
                'currency' => $this->currency,
                'unit_amount' => $unitAmount,
                // Stripe rejects an empty name and truncates a long one itself;
                // do it here so what the customer sees is what we chose.
                'product_data' => ['name' => mb_substr(trim($name) ?: 'Item', 0, 120)],
            ],
        ];
    }

    /** An amount in whatever unit Stripe charges this currency in. */
    private function minorUnits(string $amount): int
    {
        $value = (float) str_replace(',', '', $amount);

        return in_array($this->currency, self::ZERO_DECIMAL, true)
            ? (int) round($value)
            : (int) round($value * 100);
    }

    private function client(): PendingRequest
    {
        $request = Http::withToken($this->secret)->acceptJson()->timeout(20);

        // Left unset, Stripe answers in whatever version the account is pinned
        // to, which is the sane default; setting it makes upgrades deliberate.
        return $this->apiVersion ? $request->withHeaders(['Stripe-Version' => $this->apiVersion]) : $request;
    }
}
