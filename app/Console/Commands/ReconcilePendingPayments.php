<?php

namespace App\Console\Commands;

use App\Models\Sales;
use App\Repository\OrderRepository;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentGateways;
use App\Services\Payments\PaymentSettlement;
use Illuminate\Console\Command;

/**
 * Settles what the browser never told us.
 *
 * eSewa has no webhook - the only word that a payment went through rides back
 * on the customer's own browser. Close the tab on the way back, lose the
 * connection, run out of battery, and the money is gone from their account
 * while the order sits in 'pending_payment' for ever, holding its stock.
 *
 * Stripe does have a webhook, so this is a second line of defence there rather
 * than the only one: a webhook endpoint that was down, misconfigured, or behind
 * an expired signing secret leaves exactly the same orphan, and this finds it.
 *
 * So we ask the gateway ourselves, on a schedule. Every order still mid-payment
 * is put to whichever gateway it was placed through: paid ones are settled
 * exactly as the callback would have settled them, and ones the gateway has no
 * payment for are given up on once they are old enough to be certainly
 * abandoned - which is also what stops an abandoned checkout from sitting on
 * stock nobody can buy.
 */
class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile
                            {--minutes=10 : Leave orders younger than this alone - the customer may still be at the gateway}
                            {--expire=45 : Cancel an unpaid order once it is this old}
                            {--dry-run : Report what would happen, change nothing}';

    protected $description = 'Ask each gateway about orders stuck mid-payment; settle the paid ones, cancel the abandoned ones';

    public function handle(
        OrderRepository $orders,
        PaymentGateways $gateways,
        PaymentSettlement $settlement,
    ): int {
        $minutes = max(0, (int) $this->option('minutes'));
        $expire = max($minutes, (int) $this->option('expire'));
        $dry = (bool) $this->option('dry-run');

        $pending = $orders->pendingPayments($minutes)->orderBy('id')->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing mid-payment.');

            return self::SUCCESS;
        }

        $this->line("Checking {$pending->count()} pending order(s)" . ($dry ? ' (dry run)' : ''));

        $settled = $cancelled = $waiting = $unreachable = $orphaned = 0;

        foreach ($pending as $order) {
            $gateway = $gateways->for($order);

            // An order placed through something this build no longer has. Left
            // alone on purpose: guessing at it could cancel a paid order.
            if (! $gateway) {
                $orphaned++;
                $this->warn("  #{$order->id} {$order->payment_uuid} - unknown gateway '{$order->payment_method}', skipping");

                continue;
            }

            $total = $settlement->total($order);
            $result = $gateway->fetchStatus($order, $total);
            $age = (int) $order->created_at->diffInMinutes(now());
            $name = $gateway->key();

            if ($result['state'] === PaymentGateway::STATUS_COMPLETE) {
                $done = $dry ? true : $settlement->settle($order, $result['reference']);
                $settled += $done ? 1 : 0;

                $this->line("  #{$order->id} {$order->payment_uuid} - paid ({$name}), settling");
                $this->record($settlement, $dry, $name, 'settled by reconcile', $order, [
                    'total' => $total,
                    'ref' => $result['reference'],
                    'age_minutes' => $age,
                ]);

                continue;
            }

            if ($result['state'] === PaymentGateway::STATUS_UNKNOWN) {
                $unreachable++;
                $this->warn("  #{$order->id} {$order->payment_uuid} - {$name} unreachable, leaving it");
                $this->record($settlement, $dry, $name, 'reconcile could not reach gateway', $order, [
                    'age_minutes' => $age,
                ]);

                continue;
            }

            // The gateway has no completed payment for it. Young orders are left
            // be - the customer may be part-way through - and old ones are let go.
            if ($age < $expire) {
                $waiting++;
                $this->line("  #{$order->id} {$order->payment_uuid} - unpaid, {$age}m old, still waiting");

                continue;
            }

            $gone = $dry ? true : $settlement->fail($order);
            $cancelled += $gone ? 1 : 0;

            $this->line("  #{$order->id} {$order->payment_uuid} - unpaid after {$age}m, cancelling");
            $this->record($settlement, $dry, $name, 'expired by reconcile', $order, [
                'gateway_said' => $result['reported'],
                'age_minutes' => $age,
            ]);
        }

        $this->newLine();
        $this->info("settled {$settled}, cancelled {$cancelled}, still waiting {$waiting}, unreachable {$unreachable}, skipped {$orphaned}");

        return self::SUCCESS;
    }

    private function record(PaymentSettlement $settlement, bool $dry, string $gateway, string $event, Sales $order, array $context): void
    {
        if ($dry) {
            return;
        }

        $settlement->log($gateway, $event, $order->payment_uuid, ['order_id' => $order->id] + $context);
    }
}
