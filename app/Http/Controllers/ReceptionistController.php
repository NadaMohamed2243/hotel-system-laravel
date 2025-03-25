<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Receptionist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;


class ReceptionistController extends Controller
{

    public function create()
    {
        $managers = User::where('role', 'manager')
        ->select('id', 'name', 'email')
        ->get();

        return inertia('Admin/Receptionists/Create', [
        'managers' => $managers
        ]);
    }
    


    public function destroy(Receptionist $receptionist)
    {
        if (Auth::user()->role === 'manager' && $receptionist->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to delete this receptionist.');
        }

        $receptionist->delete();
        return redirect()->back()->with('success', 'Receptionist deleted successfully.');
    }

    public function index()
    {
        try {
            if (!auth()->user()->hasPermissionTo('manage receptionists')) {
                return abort(403, 'You do not have permission to view receptionists.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'manager') {
                return abort(403, 'Unauthorized action.');
            }
        }
    
        // Fetch receptionists with relationships
        $query = Receptionist::with(['user', 'manager']);
        
        if (Auth::user()->role !== 'admin') {
            $query->where('manager_id', Auth::id());
        }
    
        $receptionists = $query->get()->map(function ($receptionist) {
            return [
                // Receptionist data
                'id' => $receptionist->id,
                'is_banned' => $receptionist->is_banned,
                'created_at' => $receptionist->created_at->format('Y-m-d H:i:s'),
                
                // User data
                'name' => $receptionist->user->name,
                'email' => $receptionist->user->email,
                'national_id' => $receptionist->user->national_id,
                'avatar' => $receptionist->user->avatar_image ? 
                    asset('storage/' . $receptionist->user->avatar_image) : null,
                
                // Manager data
                'manager_name' => $receptionist->manager->name,
                'manager_email' => $receptionist->manager->email,
            ];
        });
    
        $managers = User::where('role', 'manager')
                ->select('id', 'name', 'email')
                ->get();
        
        return Inertia::render('Receptionists/Index', [
            'receptionists' => $receptionists,
            'managers' => $managers
            ]);
    }

    public function show(Receptionist $receptionist)
    {
        return Inertia::render('Admin/Receptionists/Show', [
            'receptionist' => $receptionist->load('user', 'manager'),
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'national_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'avatar_image' => 'nullable|image|max:2048',
            'manager_id' => 'required|exists:users,id'
        ]);

        // Handle avatar upload
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id'],
            'password' => bcrypt($validated['password']),
            'role' => 'receptionist',
            'avatar_image' => $avatarPath,
        ]);

        // Create receptionist record
        $receptionist = Receptionist::create([
            'user_id' => $user->id,
            'manager_id' => $validated['manager_id'],
        ]);

        return redirect()->route('admin.receptionists.index')
            ->with('success', 'Receptionist added successfully');
    }


    // ReceptionistController.php
    public function edit(Receptionist $receptionist)
    {
        return response()->json([
            'receptionist' => $receptionist->load('user', 'manager'),
            'managers' => User::where('role', 'manager')->get(['id', 'name', 'email'])
        ]);
    }

    public function update(Request $request, Receptionist $receptionist)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$receptionist->user_id,
            'national_id' => 'nullable|string|max:255',
            'manager_id' => 'required|exists:users,id',
            'is_banned' => 'boolean',
            'avatar' => 'nullable|image|max:2048'
        ]);

        // Update user
        $receptionist->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id']
        ]);

        // Update avatar if provided
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $receptionist->user->update(['avatar_image' => $path]);
        }

        // Update receptionist
        $receptionist->update([
            'manager_id' => $validated['manager_id'],
            'is_banned' => $validated['is_banned']
        ]);

        return redirect()->back()->with('success', 'Receptionist updated successfully');
    }

    public function ban(Receptionist $receptionist)
    {
        if (Auth::user()->role === 'manager' && $receptionist->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to ban this receptionist.');
        }
    
        $receptionist->update([
            'is_banned' => true,
            'banned_at' => now(), 
        ]);
            return redirect()->back()->with('success', 'Receptionist banned successfully.');
    }
    
    public function unban(Receptionist $receptionist)
    {
        if (Auth::user()->role === 'manager' && $receptionist->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to unban this receptionist.');
        }
    
        $receptionist->update([
            'is_banned' => false,
            'banned_at' => null, 
        ]);
            return redirect()->back()->with('success', 'Receptionist unbanned successfully.');
    }

}



