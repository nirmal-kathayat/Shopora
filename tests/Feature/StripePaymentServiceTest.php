<?php

namespace Tests\Feature;

use App\Models\Sales;
use App\Services\Payments\CheckoutRequest;
use App\Services\Payments\Handoff;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentGatewayException;
use App\Services\Payments\StripePaymentService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The Stripe client, with Stripe itself faked.
 *
 * Nothing here touches the database: what is worth pinning down is the shape of
 * what we send Stripe, and - far more important - what we are prepared to
 * believe from it. Every test below that says "reject" is a way an order could
 * otherwise be settled without the money arriving.
 */
class StripePaymentServiceTest extends TestCase
{
    private const SECRET = 'sk_test_pretend';

    private const WEBHOOK_SECRET = 'whsec_pretend';

    private function stripe(string $currency = 'npr'): StripePaymentService
    {
        return new StripePaymentService(
            self::SECRET,
            self::WEBHOOK_SECRET,
            $currency,
            'https://api.stripe.com/v1',
        );
    }

    private function order(): Sales
    {
        $order = new Sales();
        $order->id = 42;
        $order->payment_uuid = '42-ABC123';

        return $order;
    }

    private function checkoutRequest(array $lines, string $delivery, string $total): CheckoutRequest
    {
        return new CheckoutRequest(
            order: $this->order(),
            uuid: '42-ABC123',
            amount: '1650',
            delivery: $delivery,
            total: $total,
            lines: $lines,
        );
    }

    public function test_it_hands_back_the_session_url_and_id(): void
    {
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ]),
        ]);

        $handoff = $this->stripe()->checkout($this->checkoutRequest(
            [['name' => 'Wireless Mouse', 'qty' => 1, 'unit_amount' => 1200.0]],
            '100',
            '1300',
        ));

        $this->assertSame(Handoff::REDIRECT, $handoff->mode);
        $this->assertSame('https://checkout.stripe.com/c/pay/cs_test_123', $handoff->action);
        $this->assertSame('cs_test_123', $handoff->session);
    }

    public function test_it_itemises_the_order_in_paisa_and_charges_delivery_separately(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://x'])]);

        $this->stripe()->checkout($this->checkoutRequest([
            ['name' => 'Wireless Mouse', 'qty' => 1, 'unit_amount' => 1200.0],
            ['name' => 'Sugar 1kg', 'qty' => 2, 'unit_amount' => 140.0],
        ], '100', '1580'));

        Http::assertSent(function (Request $request) {
            $items = $request->data()['line_items'];

            $this->assertCount(3, $items);
            $this->assertSame(120000, $items[0]['price_data']['unit_amount']);
            $this->assertSame(14000, $items[1]['price_data']['unit_amount']);
            $this->assertSame(2, $items[1]['quantity']);
            $this->assertSame('Delivery', $items[2]['price_data']['product_data']['name']);
            $this->assertSame(10000, $items[2]['price_data']['unit_amount']);
            $this->assertSame('npr', $items[0]['price_data']['currency']);

            return true;
        });
    }

    public function test_it_falls_back_to_one_line_when_the_lines_do_not_add_up(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://x'])]);

        // A total that the lines cannot account for. Charging what the order
        // actually comes to matters more than showing it itemised.
        $this->stripe()->checkout($this->checkoutRequest(
            [['name' => 'Wireless Mouse', 'qty' => 1, 'unit_amount' => 1200.0]],
            '0',
            '1580',
        ));

        Http::assertSent(function (Request $request) {
            $items = $request->data()['line_items'];

            $this->assertCount(1, $items);
            $this->assertSame(158000, $items[0]['price_data']['unit_amount']);

            return true;
        });
    }

    public function test_it_carries_our_uuid_and_a_signed_cancel_link(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://x'])]);

        $stripe = $this->stripe();
        $stripe->checkout($this->checkoutRequest([], '100', '1300'));

        Http::assertSent(function (Request $request) use ($stripe) {
            $body = $request->data();

            $this->assertSame('42-ABC123', $body['client_reference_id']);
            $this->assertStringContainsString('{CHECKOUT_SESSION_ID}', $body['success_url']);
            $this->assertStringContainsString(
                'sig=' . $stripe->callbackToken('42-ABC123'),
                urldecode($body['cancel_url'])
            );
            $this->assertSame('Bearer ' . self::SECRET, $request->header('Authorization')[0]);

            return true;
        });
    }

    public function test_it_refuses_to_pretend_a_failed_session_is_a_payment_page(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['error' => ['message' => 'No such price']], 400)]);

        $this->expectException(PaymentGatewayException::class);

        $this->stripe()->checkout($this->checkoutRequest([], '0', '1300'));
    }

    public function test_a_zero_decimal_currency_is_not_multiplied_by_a_hundred(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response(['id' => 'cs_1', 'url' => 'https://x'])]);

        $this->stripe('jpy')->checkout($this->checkoutRequest([], '0', '1300'));

        Http::assertSent(function (Request $request) {
            $this->assertSame(1300, $request->data()['line_items'][0]['price_data']['unit_amount']);

            return true;
        });
    }

    public function test_a_session_only_counts_as_paid_for_the_amount_we_expected(): void
    {
        $stripe = $this->stripe();

        $paid = ['payment_status' => 'paid', 'amount_total' => 130000];

        $this->assertTrue($stripe->sessionIsPaid($paid, '1300'));
        // A payment for a different order's total must not settle this one.
        $this->assertFalse($stripe->sessionIsPaid($paid, '1400'));
        $this->assertFalse($stripe->sessionIsPaid(['payment_status' => 'unpaid', 'amount_total' => 130000], '1300'));
    }

    public function test_an_order_with_no_session_was_never_paid(): void
    {
        $status = $this->stripe()->fetchStatus($this->order(), '1300');

        $this->assertSame(PaymentGateway::STATUS_INCOMPLETE, $status['state']);
    }

    public function test_an_unreachable_stripe_is_not_a_no(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response('', 503)]);

        $order = $this->order();
        $order->payment_session = 'cs_test_123';

        $status = $this->stripe()->fetchStatus($order, '1300');

        // Cancelling an order because Stripe had a bad minute would hand back
        // stock for a payment that went through.
        $this->assertSame(PaymentGateway::STATUS_UNKNOWN, $status['state']);
    }

    public function test_it_reads_a_paid_session_back_with_its_charge_reference(): void
    {
        Http::fake(['api.stripe.com/*' => Http::response([
            'id' => 'cs_test_123',
            'payment_status' => 'paid',
            'amount_total' => 130000,
            'payment_intent' => 'pi_test_9',
        ])]);

        $order = $this->order();
        $order->payment_session = 'cs_test_123';

        $status = $this->stripe()->fetchStatus($order, '1300');

        $this->assertSame(PaymentGateway::STATUS_COMPLETE, $status['state']);
        $this->assertSame('pi_test_9', $status['reference']);
    }

    public function test_it_accepts_a_correctly_signed_webhook(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => []]]);

        $event = $this->stripe()->verifyWebhook($payload, $this->signature($payload));

        $this->assertSame('checkout.session.completed', $event['type']);
    }

    public function test_it_rejects_a_webhook_whose_body_was_changed(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed']);
        $signature = $this->signature($payload);

        $this->assertNull($this->stripe()->verifyWebhook($payload . ' ', $signature));
    }

    public function test_it_rejects_a_forged_or_stale_webhook(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed']);
        $stripe = $this->stripe();

        $this->assertNull($stripe->verifyWebhook($payload, 't=' . time() . ',v1=deadbeef'));
        $this->assertNull($stripe->verifyWebhook($payload, $this->signature($payload, time() - 3600)));
        $this->assertNull($stripe->verifyWebhook($payload, null));
    }

    public function test_a_cancel_token_is_only_good_for_its_own_order(): void
    {
        $stripe = $this->stripe();

        $this->assertTrue($stripe->callbackTokenMatches('42-ABC123', $stripe->callbackToken('42-ABC123')));
        $this->assertFalse($stripe->callbackTokenMatches('43-XYZ999', $stripe->callbackToken('42-ABC123')));
        $this->assertFalse($stripe->callbackTokenMatches('42-ABC123', null));
    }

    private function signature(string $payload, ?int $timestamp = null): string
    {
        $timestamp ??= time();

        return 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, self::WEBHOOK_SECRET);
    }
}
