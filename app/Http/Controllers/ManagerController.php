<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class ManagerController extends Controller
{
    /**
     * Controller for managing managers
     */
    public function index()
    {
        // Check permission manually rather than in middleware to avoid errors
        try {
            if (!auth()->user()->hasPermissionTo('manage managers')) {
                return abort(403, 'You do not have permission to view managers.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            // Fallback to role check if permission check fails - only admin can see managers
            if (auth()->user()->role !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        }
        
        $managers = User::where('role', 'manager')->get();
        
        // Transform managers to include full avatar URL
        $managers->transform(function($manager) {
            if ($manager->avatar_image) {
                $manager->avatar = asset('storage/' . $manager->avatar_image);
            }
            return $manager;
        });
        
        return Inertia::render('Managers/Index', ['managers' => $managers]);
    }

    public function store(Request $request)
    {
        // Check permission manually
        try {
            if (!auth()->user()->hasPermissionTo('manage managers')) {
                return abort(403, 'You do not have permission to create managers.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            // Fallback to role check if permission check fails
            if (auth()->user()->role !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Rules\Password::defaults()],
            'national_id' => 'nullable|string|unique:users,national_id',
            'role' => 'required|in:manager',
            'avatar' => 'nullable|image|mimes:jpeg,jpg',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'national_id' => $request->national_id,
            'role' => 'manager',
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar_image'] = $avatarPath;
        }

        $user = User::create($userData);

        // Assign manager role using Spatie permissions
        try {
            $user->assignRole('manager');
        } catch (\Exception $e) {
            Log::warning('Role assignment failed: ' . $e->getMessage());
        }

        Manager::create([
            'user_id' => $user->id,
        ]);

        $managers = $this->getFormattedManagers();

        return redirect()->route('managers.index')
            ->with('success', 'Manager created successfully')
            ->with('managers', $managers);
    }

    public function destroy(User $user)
    {
        // Check permission manually
        try {
            if (!auth()->user()->hasPermissionTo('manage managers')) {
                return abort(403, 'You do not have permission to delete managers.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            // Fallback to role check if permission check fails
            if (auth()->user()->role !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        }

        // Delete old avatar if exists
        if ($user->avatar_image) {
            Storage::disk('public')->delete($user->avatar_image);
        }
        
        // Remove role before deleting
        try {
            $user->removeRole('manager');
        } catch (\Exception $e) {
            Log::warning('Role removal failed: ' . $e->getMessage());
        }
        
        $user->delete();

        $managers = $this->getFormattedManagers();

        return redirect()->route('managers.index')
            ->with('success', 'Manager deleted successfully')
            ->with('managers', $managers);
    }

    public function edit(User $user)
    {
        // Check permission manually or if editing own profile
        try {
            $hasPermission = auth()->user()->hasPermissionTo('manage managers');
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            $hasPermission = auth()->user()->role === 'admin';
        }
        
        if (!$hasPermission && auth()->id() !== $user->id) {
            return abort(403, 'Unauthorized action.');
        }

        if ($user->avatar_image) {
            $user->avatar = asset('storage/' . $user->avatar_image);
        }
        
        return Inertia::render('Managers/Edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        // Check permission manually or if editing own profile
        try {
            $hasPermission = auth()->user()->hasPermissionTo('manage managers');
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            $hasPermission = auth()->user()->role === 'admin';
        }
        
        if (!$hasPermission && auth()->id() !== $user->id) {
            return abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'national_id' => 'nullable|string|unique:users,national_id,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,jpg',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => $request->national_id,
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_image) {
                Storage::disk('public')->delete($user->avatar_image);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar_image'] = $avatarPath;
        } 
        // Handle avatar removal
        else if ($request->boolean('remove_avatar') && $user->avatar_image) {
            Storage::disk('public')->delete($user->avatar_image);
            $userData['avatar_image'] = null;
        }

        $user->update($userData);

        $managers = $this->getFormattedManagers();

        return redirect()->route('managers.index')
            ->with('success', 'Manager updated successfully')
            ->with('managers', $managers);
    }

    private function getFormattedManagers()
    {
        $managers = User::where('role', 'manager')->get();
        
        $managers->transform(function($manager) {
            if ($manager->avatar_image) {
                $manager->avatar = asset('storage/' . $manager->avatar_image);
            }
            return $manager;
        });
        
        return $managers;
    }
}
