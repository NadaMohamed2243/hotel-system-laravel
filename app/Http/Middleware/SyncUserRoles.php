<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Symfony\Component\HttpFoundation\Response;

class SyncUserRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            try {
                // Map the existing 'role' field to Spatie roles if needed
                if (!$user->hasRole($user->role)) {
                    // Remove any previous roles
                    $user->syncRoles([$user->role]);
                }
            } catch (\Exception $e) {
                // Log the error but don't break the application
                Log::error('Error syncing user roles: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
} 