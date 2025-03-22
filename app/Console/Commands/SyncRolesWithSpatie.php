<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncRolesWithSpatie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize existing users\' roles with Spatie roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role synchronization...');
        
        // Check if roles exist in Spatie, otherwise create them
        $roles = ['admin', 'manager', 'receptionist', 'client'];
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                $this->warn("Role '$roleName' not found in Spatie roles. Please run php artisan db:seed --class=RolesAndPermissionsSeeder first.");
                if ($this->confirm('Do you want to continue anyway?', true)) {
                    Role::create(['name' => $roleName]);
                    $this->info("Created role '$roleName'");
                } else {
                    return 1;
                }
            }
        }

        // Get all users
        $users = User::all();
        $this->info("Found {$users->count()} users to process.");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($users as $user) {
            if (in_array($user->role, $roles)) {
                // Sync user role with Spatie roles (remove all other roles first)
                $user->syncRoles([$user->role]);
                $updated++;
            } else {
                $this->newLine();
                $this->warn("User ID {$user->id} has invalid role: {$user->role}");
                $skipped++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Role synchronization completed:");
        $this->info("- {$updated} users updated with Spatie roles");
        $this->info("- {$skipped} users skipped (invalid roles)");
        
        return 0;
    }
}
