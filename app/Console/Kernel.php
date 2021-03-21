<?php

namespace App\Console;

use App\Console\Commands\QueueNotifications;
use App\Console\Commands\SendDiscordNotifications;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SendDiscordNotifications::class,
        QueueNotifications::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command(QueueNotifications::class)
            ->hourly()
            ->onOneServer()
            ->environments(['production']);

        $schedule->command(SendDiscordNotifications::class)
            ->everyFifteenMinutes()
            ->onOneServer()
            ->environments(['production']);
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
