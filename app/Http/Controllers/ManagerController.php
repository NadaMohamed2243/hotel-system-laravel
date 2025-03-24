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
use App\Http\Requests\StoreUserRequest;
class ManagerController extends Controller
{
    /**
     * Controller for managing managers
     */
    public function index()
    {
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

    public function store(StoreUserRequest $request)
    {
    
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

        $user->assignRole('manager');        

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
        // Delete old avatar if exists
        if ($user->avatar_image) {
            Storage::disk('public')->delete($user->avatar_image);
        }
        
        $user->removeRole('manager');
        
        $user->delete();

        $managers = $this->getFormattedManagers();

        return redirect()->route('managers.index')
            ->with('success', 'Manager deleted successfully')
            ->with('managers', $managers);
    }

    // public function edit(User $user)
    // {
    //     // Allow users to edit their own profile
    //     if (auth()->id() !== $user->id) {
    //         // The permission check for managing other managers is handled by middleware
    //         // This just ensures a manager can edit their own profile
    //         abort_if($user->role !== 'manager', 403, 'Unauthorized action.');
    //     }

    //     if ($user->avatar_image) {
    //         $user->avatar = asset('storage/' . $user->avatar_image);
    //     }
        
    //     return Inertia::render('Managers/Edit', ['user' => $user]);
    // }

    public function update(StoreUserRequest $request, User $user)
    {
        if (auth()->id() !== $user->id) {
            abort_if($user->role !== 'manager', 403, 'Unauthorized action.');
        }

       
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => $request->national_id,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar_image) {
                Storage::disk('public')->delete($user->avatar_image);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar_image'] = $avatarPath;
        } 
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
