<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Define your scheduled command
Schedule::command('login:reminder')
    ->daily() // Runs once daily at midnight
    ->at('09:00') // Or specify exact time
    ->timezone('Africa/Cairo') // Set your timezone
    ->emailOutputOnFailure('admin@example.com'); // Optional error notifications
