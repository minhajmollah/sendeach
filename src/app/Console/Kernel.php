<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('check-processing:desktop-messages')
            ->everyMinute();

        $schedule->command('flush:inactive-web-whatsapp-session')
            ->everyTwoHours();

        $schedule->command('resend:mobile-sms')->everyFifteenMinutes();

        $schedule->call(function () {
            \DB::table('users')->update(['default_limit' => 200]);
        })->dailyAt('00:00');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
