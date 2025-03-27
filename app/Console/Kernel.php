<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Add your login reminder command to run daily at 9:00 AM
        $schedule->command('login:reminder')
        ->daily() // Runs once daily at midnight
        ->at('09:00') // Or specify exact time
        ->timezone('Africa/Cairo') // Set your timezone
        ->emailOutputOnFailure('admin@example.com'); // Optional error notifications
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
