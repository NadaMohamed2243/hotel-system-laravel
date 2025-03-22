<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin3@admin.com',
            'password' => Hash::make('123456'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        // Assign the admin role using Spatie permissions
        $admin->assignRole('admin');
    }
}
