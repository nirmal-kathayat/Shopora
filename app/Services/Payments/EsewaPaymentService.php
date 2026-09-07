<?php

namespace App\Services\Payments;

use App\Models\Sales;
use Illuminate\Support\Facades\Http;

/**
 * eSewa ePay v2. The whole scheme rests on an HMAC-SHA256 signature over a
 * fixed, comma-joined "field=value" string: we sign the form we send, and eSewa
 * signs the response it sends back, so neither side can tamper with the amount
 * or the reference in flight.
 *
 * eSewa has no webhook. The only word that a payment went through rides back on
 * the customer's own browser, which is why verifyCallback() trusts nothing it is
 * not given a signature for, and why payments:reconcile exists at all.
 *
 * @see https://developer.esewa.com.np/pages/Epay#integration
 */
class EsewaPaymentService implements PaymentGateway
{
    use SignsCallbacks;

    public function __construct(
        private readonly string $productCode,
        private readonly string $secret,
        private readonly string $formUrl,
        private readonly string $statusUrl,
    ) {
    }

    public static function fromConfig(): self
    {
        $c = config('services.esewa');

        return new self($c['product_code'], $c['secret'], $c['form_url'], $c['status_url']);
    }

    public function key(): string
    {
        return 'esewa';
    }

    public function title(): string
    {
        return 'eSewa';
    }

    public function configured(): bool
    {
        return $this->productCode !== '' && $this->secret !== '';
    }

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function formUrl(): string
    {
        return $this->formUrl;
    }

    /**
     * eSewa is paid by POSTing a form at it, so the handover is that form:
     * every field it needs, signature included. The amounts are passed through
     * as the strings we were given, so the exact characters we sign are the
     * exact characters we post - eSewa compares the two verbatim.
     */
    public function checkout(CheckoutRequest $request): Handoff
    {
        $signature = $this->sign([
            'total_amount' => $request->total,
            'transaction_uuid' => $request->uuid,
            'product_code' => $this->productCode,
        ]);

        return Handoff::post($this->formUrl, [
            'amount' => $request->amount,
            'tax_amount' => '0',
            'total_amount' => $request->total,
            'transaction_uuid' => $request->uuid,
            'product_code' => $this->productCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => $request->delivery,
            'success_url' => route('payment.esewa.success'),
            'failure_url' => route('payment.esewa.failure', [
                'oid' => $request->uuid,
                'sig' => $this->callbackToken($request->uuid),
            ]),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => $signature,
        ]);
    }

    /**
     * Verify the base64 JSON eSewa appends to the success URL. Returns the
     * decoded payload when the signature over its own signed_field_names checks
     * out and it reports COMPLETE; null otherwise. The signature is what makes
     * this trustworthy despite arriving through the customer's browser.
     */
    public function verifyCallback(?string $encoded): ?array
    {
        if (! $encoded) {
            return null;
        }

        $json = base64_decode($encoded, true);
        if ($json === false) {
            return null;
        }

        $payload = json_decode($json, true);
        if (! is_array($payload) || ($payload['status'] ?? null) !== 'COMPLETE') {
            return null;
        }

        $fields = explode(',', $payload['signed_field_names'] ?? '');
        $data = [];
        foreach ($fields as $field) {
            $field = trim($field);
            if ($field === '' || ! array_key_exists($field, $payload)) {
                return null;
            }
            $data[$field] = $payload[$field];
        }

        $expected = $this->sign($data);
        if (! hash_equals($expected, (string) ($payload['signature'] ?? ''))) {
            return null;
        }

        return $payload;
    }

    /**
     * Ask eSewa's server directly whether this transaction completed, and get
     * its own reference for it when there is one - what a reconciliation run
     * needs, since it never sees the callback that would otherwise carry it.
     */
    public function fetchStatus(Sales $order, string $totalAmount): array
    {
        try {
            $response = Http::acceptJson()->timeout(15)->get($this->statusUrl, [
                'product_code' => $this->productCode,
                'total_amount' => $totalAmount,
                'transaction_uuid' => (string) $order->payment_uuid,
            ]);
        } catch (\Throwable $e) {
            return ['state' => self::STATUS_UNKNOWN, 'reference' => null, 'reported' => null];
        }

        if (! $response->ok()) {
            return ['state' => self::STATUS_UNKNOWN, 'reference' => null, 'reported' => null];
        }

        $reported = $response->json('status');

        $complete = $reported === 'COMPLETE'
            && $this->amountsMatch((string) $response->json('total_amount'), $totalAmount);

        return [
            'state' => $complete ? self::STATUS_COMPLETE : self::STATUS_INCOMPLETE,
            'reference' => $response->json('ref_id'),
            'reported' => is_string($reported) ? $reported : null,
        ];
    }

    /**
     * eSewa may hand an amount back with a thousands separator ("1,000.0") or a
     * trailing zero, so compare on the numeric value, not the string.
     */
    public function amountsMatch(string $a, string $b): bool
    {
        return abs((float) str_replace(',', '', $a) - (float) str_replace(',', '', $b)) < 0.01;
    }

    protected function callbackSecret(): string
    {
        return $this->secret;
    }

    private function sign(array $data): string
    {
        $message = implode(',', array_map(
            fn ($key) => "{$key}={$data[$key]}",
            array_keys($data)
        ));

        return base64_encode(hash_hmac('sha256', $message, $this->secret, true));
    }
}
