<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        Log::info('Scheduling notifications:send command');
        $schedule->command('notifications:send')->everyMinute();

         // Daily
            $schedule->call(function () {
                $admins = \App\Models\Admin::all();
                $emailService = new \App\Services\AdminNotificationEmailService();

                foreach ($admins as $admin) {
                    $emailService->sendDailyDigest($admin);
                }
            })->dailyAt('08:00');

            // Weekly
            $schedule->call(function () {
                $admins = \App\Models\Admin::all();
                $emailService = new \App\Services\AdminNotificationEmailService();

                foreach ($admins as $admin) {
                    $emailService->sendWeeklyDigest($admin);
                }
            })->weeklyOn(1, '09:00');

            // Monthly
            $schedule->call(function () {
                $admins = \App\Models\Admin::all();
                $emailService = new \App\Services\AdminNotificationEmailService();

                foreach ($admins as $admin) {
                    $emailService->sendMonthlyDigest($admin);
                }
            })->monthlyOn(1, '10:00');

            $schedule->command('system:monitor-critical')->everyFiveMinutes();
            $schedule->command('platform:detect-code-changes')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
