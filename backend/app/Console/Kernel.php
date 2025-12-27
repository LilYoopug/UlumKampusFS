<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel for Laravel 11
 *
 * Laravel 11 removed the Console Kernel, but the test framework still expects it.
 * This minimal stub satisfies the test framework requirements.
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // $this->load(__DIR__.'/Commands');
    }
}