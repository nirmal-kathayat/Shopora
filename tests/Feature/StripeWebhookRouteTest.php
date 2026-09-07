<?php

namespace Tests\Feature;

use App\Services\Payments\StripePaymentService;
use Tests\TestCase;

/**
 * The webhook endpoint itself: who gets in, and what Stripe is told.
 *
 * The rule the route has to hold to is that an unsigned or wrongly signed body
 * never reaches the settlement code, and that anything which does verify is
 * answered 200 even when we do nothing with it - a non-2xx tells Stripe to
 * redeliver, and an event we have no use for would be retried for days.
 *
 * No order is created here on purpose: settling one is covered where the
 * decision actually lives, and a test that writes orders into the shop's own
 * database is not one worth having.
 */
class StripeWebhookRouteTest extends TestCase
{
    private const WEBHOOK_SECRET = 'whsec_route_test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.secret' => 'sk_test_route',
            'services.stripe.webhook_secret' => self::WEBHOOK_SECRET,
        ]);

        // The client is a singleton built from config, so it has to be dropped
        // for the settings above to be the ones the route uses.
        $this->app->forgetInstance(StripePaymentService::class);
    }

    public function test_an_unsigned_webhook_is_turned_away(): void
    {
        $this->postJson('/api/payment/stripe/webhook', ['type' => 'checkout.session.completed'])
            ->assertStatus(400);
    }

    public function test_a_wrongly_signed_webhook_is_turned_away(): void
    {
        $payload = json_encode(['type' => 'checkout.session.completed']);

        $this->call(
            'POST',
            '/api/payment/stripe/webhook',
            server: ['HTTP_STRIPE_SIGNATURE' => 't=' . time() . ',v1=' . str_repeat('0', 64), 'CONTENT_TYPE' => 'application/json'],
            content: $payload,
        )->assertStatus(400);
    }

    public function test_an_event_we_do_not_act_on_is_acknowledged(): void
    {
        // Acknowledged, not acted on: Stripe sends plenty we did not subscribe
        // to, and refusing them would have it retrying for days.
        $this->send(['type' => 'payment_intent.created', 'data' => ['object' => []]])
            ->assertStatus(200);
    }

    public function test_a_signed_event_for_an_unknown_order_is_acknowledged(): void
    {
        $this->send([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'client_reference_id' => 'no-such-order',
                'payment_status' => 'paid',
                'amount_total' => 130000,
            ]],
        ])->assertStatus(200);
    }

    private function send(array $event): \Illuminate\Testing\TestResponse
    {
        $payload = json_encode($event);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, self::WEBHOOK_SECRET);

        return $this->call(
            'POST',
            '/api/payment/stripe/webhook',
            server: [
                'HTTP_STRIPE_SIGNATURE' => 't=' . $timestamp . ',v1=' . $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            content: $payload,
        );
    }
}
