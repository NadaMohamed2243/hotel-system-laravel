<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        //
        if ($user && $user->client) {
            // 
            $user->load('client');

            // 
            if ($user->client->status !== 'approved') {
                return redirect()->route('pending.approval');
            }

            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
