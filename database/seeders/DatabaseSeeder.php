<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First create roles and permissions
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);
        
        // Create admin user
        $this->call([
            AdminSeeder::class,
        ]);
        
        // Create test users for each role
        // Manager
        $manager = User::create([
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('123456'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('manager');
        
        // Receptionist
        $receptionist = User::create([
            'name' => 'Test Receptionist',
            'email' => 'receptionist@example.com',
            'password' => Hash::make('123456'),
            'role' => 'receptionist',
            'email_verified_at' => now(),
        ]);
        $receptionist->assignRole('receptionist');
        
        // Client
        $client = User::create([
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'password' => Hash::make('123456'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);
        $client->assignRole('client');
    }
}
