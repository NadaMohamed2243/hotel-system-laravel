<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Receptionist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceptionistController extends Controller
{
    public function create()
    {
        return view('receptionists.create');
    }


    public function index()
    {
        if (Auth::user()->role === 'admin') {
            $receptionists = Receptionist::all();
        } else {
            $receptionists = Receptionist::where('manager_id', Auth::id())->get();
        }

        return view('receptionists.index', compact('receptionists'));
    }



    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'national_id' => 'required|string|unique:users',
            'avatar_image' => 'nullable|image|mimes:jpg,jpeg,png',
            'manager_id' => 'required|exists:users,id',
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'receptionist',
            'national_id' => $request->national_id,
            'avatar_image' => $request->file('avatar_image') ? $request->file('avatar_image')->store('avatars') : 'default.jpg',
        ]);

        // Create the receptionist
        Receptionist::create([
            'user_id' => $user->id,
            'manager_id' => $request->manager_id,
            'is_banned' => false,
        ]);

        // Redirect with a success message
        return redirect()->route('receptionists.index')->with('success', 'Receptionist created successfully.');
    }


    // Ban a receptionist
    public function ban(Receptionist $receptionist)
    {
        // Ensure the manager can only ban their own receptionists
        if (Auth::user()->role === 'manager' && $receptionist->manager_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to ban this receptionist.');
        }

        $receptionist->ban();

        return redirect()->back()->with('success', 'Receptionist banned successfully.');
    }

    // Unban a receptionist
    public function unban(Receptionist $receptionist)
    {
        // Ensure the manager can only unban their own receptionists
        if (Auth::user()->role === 'manager' && $receptionist->manager_id !==Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to unban this receptionist.');
        }

        $receptionist->unban();

        return redirect()->back()->with('success', 'Receptionist unbanned successfully.');
    }
}
