<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-07 10:12:53
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-16 10:48:05
 */


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
        $schedule->command('order:sync')->hourly();
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
