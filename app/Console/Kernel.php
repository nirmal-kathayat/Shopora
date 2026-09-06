<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clears tokens that expired more than a day ago, so the table does
        // not grow without bound.
        $schedule->command('sanctum:prune-expired --hours=24')->daily();

        // eSewa never calls us back on its own - a payment is only reported by
        // the customer's browser, which may never make it home. This asks the
        // gateway directly about anything left mid-payment, so no paid order is
        // lost and no abandoned one sits on its stock.
        $schedule->command('payments:reconcile')
            ->everyFiveMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
