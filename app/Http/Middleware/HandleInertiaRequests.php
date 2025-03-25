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
            'manage_receptionists' => 'manage receptionists',
            'manage_clients' => 'manage clients',
            'manage_floors' => 'manage floors',
            'manage_rooms' => 'manage rooms',
            'manage_reservations' => 'manage reservations',
            
            // View-only permissions
            'view_reservations' => 'view reservations',
            'view_client_reservations' => 'view client reservations',
            'view_approved_clients' => 'view approved clients',
            'view_unapproved_clients' => 'view unapproved clients',
            'approve_client' => 'approve client',
            
            // Financial and system permissions
            'view_financial_info' => 'view financial info',
            
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
