<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // $schedule->command('demo:cron')->hourly()
        // ->timezone('Asia/Jakarta')
        // ->between('8:00', '23:59');

        $schedule->command('access_door_sync_attendance:cron')
            ->everyFiveMinutes()
            ->timezone('Asia/Jakarta')
            ->between('1:00', '23:59')
            ->sentryMonitor();

	/* blok sementara by Anank */
        //$schedule->command('terminate:employees')
        //    ->everyTenMinutes()
        //    ->timezone('Asia/Jakarta')
        //    ->between('1:00', '23:59')
        //    ->sentryMonitor();

        $schedule->command('transfer:employees')
            ->everyTenMinutes()
            ->timezone('Asia/Jakarta')
            ->between('1:00', '23:59')
            ->sentryMonitor();

        /* blok sementara by Anank */
	//$schedule->command('app:activate-deactivate-users')
        //    ->everyTenMinutes()
        //    ->timezone('Asia/Jakarta')
        //    ->between('1:00', '23:59')
        //    ->sentryMonitor();

        $schedule->command('vehicle:nextMaintenance')
            ->dailyAt('09:00')
            ->timezone('Asia/Jakarta')
            ->sentryMonitor();
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
