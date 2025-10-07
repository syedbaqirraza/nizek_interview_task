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
        // Cleanup Livewire temp files every day at 00:00
        $schedule->command('cleanup:livewire-tmp')->dailyAt('00:00');

        // Run a short-lived queue worker once per minute to process jobs reliably in environments
        // where long-running workers are not desired. This runs the worker once each minute.
        $schedule->command('queue:work --once --tries=3')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
