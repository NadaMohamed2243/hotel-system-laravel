<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Client;


class RegisteredUserController extends Controller
{
    
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'mobile' => 'required|string|max:20',
        ]);
        //dd($request->mobile);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
        ]);

        //store data in client table
        Client::create([
            'user_id' => $user->id,
            'country' => $request->country,
            'gender' => $request->gender,
            'mobile' => $request->mobile,
            'status' => 'pending', // Pending
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('pending.approval');
    }
}
