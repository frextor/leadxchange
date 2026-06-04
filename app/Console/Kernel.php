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
        // Expire pending leads whose deadline has passed
        $schedule->command('leads:expire')->dailyAt('01:00');

        // Send J+15 and J+25 notation reminders (push notifications)
        $schedule->command('leads:send-reminders')->dailyAt('09:00');

        // Log leads whose 30-day rating window has expired (CCTP)
        $schedule->command('leads:close-expired-ratings')->dailyAt('03:00');
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
