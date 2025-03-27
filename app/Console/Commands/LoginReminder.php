<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;
use App\Notifications\LoginReminderNotification;

class LoginReminder extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'login:reminder';
    protected $description = 'Send email notifications to inactive clients';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        $inactiveClients = Client::query()
            ->where('status', 'approved') // Only approved clients
            ->where(function($query) use ($oneMonthAgo) {
                $query->where('last_login_at', '<', $oneMonthAgo)
                      ->orWhereNull('last_login_at');
            })
            ->with('user') // Eager load user relationship
            ->get();

        $count = 0;

        foreach ($inactiveClients as $client) {
            // Double check the user is actually a client
            if ($client->user && $client->user->role === 'client') {
                $client->user->notify(new LoginReminderNotification());
                $this->info("Sent reminder to: {$client->user->email}");
                $count++;
            }
        }

        $this->info("Completed: Sent reminders to {$count} approved clients.");

    }
}
