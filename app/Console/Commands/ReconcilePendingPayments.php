<?php

namespace App\Console\Commands;

use App\Repository\OrderRepository;
use App\Services\EsewaPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Settles what the browser never told us.
 *
 * eSewa has no webhook - the only word that a payment went through rides back
 * on the customer's own browser. Close the tab on the way back, lose the
 * connection, run out of battery, and the money is gone from their account
 * while the order sits in 'pending_payment' for ever, holding its stock.
 *
 * So we ask eSewa ourselves, on a schedule. Every order still mid-payment is
 * put to the gateway: paid ones are settled exactly as the callback would have
 * settled them, and ones eSewa has no payment for are given up on once they
 * are old enough to be certainly abandoned - which is also what stops an
 * abandoned checkout from sitting on stock nobody can buy.
 */
class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile
                            {--minutes=10 : Leave orders younger than this alone - the customer may still be at the gateway}
                            {--expire=45 : Cancel an unpaid order once it is this old}
                            {--dry-run : Report what would happen, change nothing}';

    protected $description = 'Ask eSewa about orders stuck mid-payment; settle the paid ones, cancel the abandoned ones';

    public function handle(OrderRepository $orders, EsewaPaymentService $esewa): int
    {
        $minutes = max(0, (int) $this->option('minutes'));
        $expire = max($minutes, (int) $this->option('expire'));
        $dry = (bool) $this->option('dry-run');

        $pending = $orders->pendingPayments($minutes)->orderBy('id')->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing mid-payment.');

            return self::SUCCESS;
        }

        $this->line("Checking {$pending->count()} pending order(s) with eSewa" . ($dry ? ' (dry run)' : ''));

        $settled = $cancelled = $waiting = $unreachable = 0;

        foreach ($pending as $order) {
            $total = $this->money($orders->total($order));
            $result = $esewa->fetchStatus($order->payment_uuid, $total);
            $age = (int) $order->created_at->diffInMinutes(now());

            if ($result['state'] === EsewaPaymentService::STATUS_COMPLETE) {
                $done = $dry ? true : $orders->settlePaid($order, $result['reference']);
                $settled += $done ? 1 : 0;

                $this->line("  #{$order->id} {$order->payment_uuid} - paid, settling");
                $this->record($dry, 'settled by reconcile', $order->payment_uuid, [
                    'order_id' => $order->id,
                    'total' => $total,
                    'ref' => $result['reference'],
                    'age_minutes' => $age,
                ]);

                continue;
            }

            if ($result['state'] === EsewaPaymentService::STATUS_UNKNOWN) {
                $unreachable++;
                $this->warn("  #{$order->id} {$order->payment_uuid} - eSewa unreachable, leaving it");
                $this->record($dry, 'reconcile could not reach gateway', $order->payment_uuid, [
                    'order_id' => $order->id,
                    'age_minutes' => $age,
                ]);

                continue;
            }

            // eSewa has no completed payment for it. Young orders are left be -
            // the customer may be part-way through - and old ones are let go.
            if ($age < $expire) {
                $waiting++;
                $this->line("  #{$order->id} {$order->payment_uuid} - unpaid, {$age}m old, still waiting");

                continue;
            }

            $gone = $dry ? true : $orders->failPending($order);
            $cancelled += $gone ? 1 : 0;

            $this->line("  #{$order->id} {$order->payment_uuid} - unpaid after {$age}m, cancelling");
            $this->record($dry, 'expired by reconcile', $order->payment_uuid, [
                'order_id' => $order->id,
                'gateway_said' => $result['reported'],
                'age_minutes' => $age,
            ]);
        }

        $this->newLine();
        $this->info("settled {$settled}, cancelled {$cancelled}, still waiting {$waiting}, unreachable {$unreachable}");

        return self::SUCCESS;
    }

    /** Same amount formatting the payment form and callback use. */
    private function money(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function record(bool $dry, string $event, string $uuid, array $context): void
    {
        if ($dry) {
            return;
        }

        Log::channel('payment')->info('esewa: ' . $event, ['uuid' => $uuid] + $context);
    }
}
