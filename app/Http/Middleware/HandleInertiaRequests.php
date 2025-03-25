<?php

namespace App\Http\Middleware;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        
        $user = $request->user();
        
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
                // Pass user permissions to the frontend
                'can' => $user ? $this->getUserPermissions($user) : [],
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
    
    /**
     * Get user permissions to share with the frontend
     */
    private function getUserPermissions($user): array
    {
        $permissions = [];
        $permissionChecks = [
            'manage_managers' => 'manage managers',
            'view_managers' => 'view managers',
            'manage_receptionists' => 'manage receptionists',
            'view_receptionists' => 'view receptionists',
            'manage_clients' => 'manage clients',
            'view_client_details' => 'view client details',
            'view_financial_info' => 'view financial info',
            'manage_settings' => 'manage settings',
            'view_system_logs' => 'view system logs',
            'access_admin_panel' => 'access admin panel',
            'manage_bookings' => 'manage bookings',
            'view_bookings' => 'view bookings',
            'view_rooms' => 'view rooms',
            'manage_rooms' => 'manage rooms',
            'view_reports' => 'view reports',
            'view_floors' => 'view floors',
            'manage_floors' => 'manage floors'
        ];
        
        foreach ($permissionChecks as $key => $permission) {
            try {
                $permissions[$key] = $user->hasPermissionTo($permission);
            } catch (\Exception $e) {
                // If the permission doesn't exist, log it and set to false
                Log::warning("Permission '{$permission}' check failed: " . $e->getMessage());
                $permissions[$key] = false;
            }
        }
        
        return $permissions;
    }
}
