<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Log;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions by module - simplified to role-based permissions
        $permissions = [
            // General admin permissions
            'access admin panel',
            
            // Role-based management permissions
            'manage managers',
            'manage receptionists',
            'manage clients',
            'manage floors',
            'manage rooms',
            'manage reservations',
            
            // View-only permissions
            'view reservations',
            'view client reservations', // For receptionists
            'view approved clients',
            'view unapproved clients',
            'approve client',
            
            // Financial and system permissions
            'view financial info',
            'manage settings',
            'view system logs',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            try {
                // Check if permission already exists
                if (!Permission::where('name', $permission)->exists()) {
                    Permission::create(['name' => $permission]);
                }
            } catch (\Exception $e) {
                Log::warning("Permission '{$permission}' creation error: " . $e->getMessage());
            }
        }

        // Define permission groups for each role
        $adminPermissions = Permission::all()->pluck('name')->toArray();
        
        $managerPermissions = [
            // Manager cannot manage managers
            // 'manage managers', // REMOVED - Managers shouldn't manage other managers
            
            // Manager role permissions
            'manage receptionists',
            'manage clients',
            'manage floors',
            'manage rooms',
            'manage reservations',
            
            // Basic admin access
            'access admin panel',
            
            // Financial access
            'view financial info',
        ];
        
        $receptionistPermissions = [
            // Limited client permissions
            'view approved clients',
            'view unapproved clients',
            'approve client',
            
            // Limited reservation permissions
            'view reservations',
            'view client reservations',
            
            // Basic admin access
            'access admin panel',
        ];

        // Create roles and assign permissions
        $roles = [
            'admin' => $adminPermissions,
            'manager' => $managerPermissions,
            'receptionist' => $receptionistPermissions,
            'client' => [], // Clients don't have any admin permissions
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            try {
                // Create or get the role
                $role = Role::firstOrCreate(['name' => $roleName]);
                
                // Get existing permissions
                $existingPermissions = Permission::whereIn('name', $rolePermissions)->get();
                
                // Sync permissions
                $role->syncPermissions($existingPermissions);
                
                $this->command->info("Role '{$roleName}' created with " . count($existingPermissions) . " permissions");
            } catch (\Exception $e) {
                Log::warning("Role '{$roleName}' creation error: " . $e->getMessage());
                $this->command->error("Error creating role '{$roleName}': " . $e->getMessage());
            }
        }
    }
} 