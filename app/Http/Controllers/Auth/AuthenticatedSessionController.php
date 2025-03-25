<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Receptionist;
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
    
        // Check if the user is a receptionist and is banned
        if ($user->role === 'receptionist') {
            $receptionist = Receptionist::where('user_id', $user->id)->first();

            if ($receptionist && $receptionist->is_banned) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account has been banned.',
                ]);
            }
        }


        if ($user->role === 'manager') {
            return redirect()->intended(route('manager.dashboard'));
        }
    

        // Redirect to admin dashboard if user is not a client
        if (Auth::user()->role !== 'client') {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
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
