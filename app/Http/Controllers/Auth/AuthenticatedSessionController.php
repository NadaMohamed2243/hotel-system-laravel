<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Receptionist;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Update last login time for clients
        if ($user->role === 'client') {
            Client::where('user_id', $user->id)->update(['last_login_at' => now()]);
        }

        // Check if the user is a receptionist and is banned
        if ($user->role === 'receptionist') {
            $receptionist = Receptionist::where('user_id', $user->id)->first();

            if ($receptionist && $receptionist->is_banned) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been banned.',
                ]);
            }
            return redirect()->intended(route('receptionist.dashboard'));
        }

        if ($user->role === 'manager') {
            return redirect()->intended(route('manager.dashboard'));
        }

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Default redirect for clients
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
