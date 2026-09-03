<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * eSewa ePay v2. The whole scheme rests on an HMAC-SHA256 signature over a
 * fixed, comma-joined "field=value" string: we sign the form we send, and eSewa
 * signs the response it sends back, so neither side can tamper with the amount
 * or the reference in flight.
 *
 * @see https://developer.esewa.com.np/pages/Epay#integration
 */
class EsewaPaymentService
{
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

    public function productCode(): string
    {
        return $this->productCode;
    }

    public function formUrl(): string
    {
        return $this->formUrl;
    }

    /**
     * The fields eSewa's payment form needs, signature included. The amounts
     * are passed through as strings so the exact characters we sign are the
     * exact characters we post - eSewa compares the two verbatim.
     */
    public function formFields(string $uuid, string $amount, string $deliveryCharge, string $totalAmount, string $successUrl, string $failureUrl): array
    {
        $signature = $this->sign([
            'total_amount' => $totalAmount,
            'transaction_uuid' => $uuid,
            'product_code' => $this->productCode,
        ]);

        return [
            'amount' => $amount,
            'tax_amount' => '0',
            'total_amount' => $totalAmount,
            'transaction_uuid' => $uuid,
            'product_code' => $this->productCode,
            'product_service_charge' => '0',
            'product_delivery_charge' => $deliveryCharge,
            'success_url' => $successUrl,
            'failure_url' => $failureUrl,
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => $signature,
        ];
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

    /** eSewa says the payment went through, for the amount we expected. */
    public const STATUS_COMPLETE = 'complete';

    /** eSewa says it did not (pending, cancelled, not found, wrong amount). */
    public const STATUS_INCOMPLETE = 'incomplete';

    /** We could not reach eSewa to ask - neither a yes nor a no. */
    public const STATUS_UNKNOWN = 'unknown';

    /**
     * Ask eSewa's server directly whether this transaction completed - the
     * authoritative check, independent of the redirect. Tri-state on purpose:
     * a definite "no" must block the order, but a network hiccup must not lose
     * a genuinely paid one, since the signed callback has already proven it.
     */
    public function checkStatus(string $uuid, string $totalAmount): string
    {
        try {
            $response = Http::acceptJson()->timeout(15)->get($this->statusUrl, [
                'product_code' => $this->productCode,
                'total_amount' => $totalAmount,
                'transaction_uuid' => $uuid,
            ]);
        } catch (\Throwable $e) {
            return self::STATUS_UNKNOWN;
        }

        if (! $response->ok()) {
            return self::STATUS_UNKNOWN;
        }

        $complete = $response->json('status') === 'COMPLETE'
            && $this->amountsMatch((string) $response->json('total_amount'), $totalAmount);

        return $complete ? self::STATUS_COMPLETE : self::STATUS_INCOMPLETE;
    }

    /**
     * eSewa may hand an amount back with a thousands separator ("1,000.0") or a
     * trailing zero, so compare on the numeric value, not the string.
     */
    public function amountsMatch(string $a, string $b): bool
    {
        return abs((float) str_replace(',', '', $a) - (float) str_replace(',', '', $b)) < 0.01;
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
