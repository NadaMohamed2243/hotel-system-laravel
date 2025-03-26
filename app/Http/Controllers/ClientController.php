<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(){
        $role = auth()->user()->role;
        $this->reset_custom_id();

        $clients = User::where('role', 'client')
        ->with('client')
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'custom_id' => $user->custom_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->client->mobile?? 'N/A',
                'approved_by' => $user->client && $user->client->approved_by ?User::find($user->client->approved_by)->name: 'N/A',
                'gender' => $user->client->gender ?? 'N/A',
                'status' => $user->client->status ?? 'N/A',
                'country' => $user->client->country ?? 'N/A',
            ];
        });
        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'role'=>$role,
            'loginUser'=>auth()->user()->id
        ]);
    }
    public function validateStep1(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:7|max:15',
            'password' => 'required|min:6'
        ]);
        return back();
    }

        public function store(Request $request)
        {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'country' => 'required|string|max:100',
                'phone' => 'required|string|min:7|max:15',
                'country' => 'string',
                'approved_by' => 'string',
                'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'role' => 'client',
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user['avatar_image'] = $avatarPath;
            }
            // Create client profile
            Client::create([
                'user_id' => $user->id,
                'mobile' => $validated['phone'],
                'gender' => $request->gender,
                'status' => $request->status,
                'country' => $validated['country'],
                'approved_by' => $validated['approved_by'],
            ]);
            $this->reset_custom_id();
            return redirect()->back();
        }

        public function destroy($id)
        {
            $user = User::find($id);
            $client = Client::where('user_id', '=', $id)->first();
            if (!$user) {
                return redirect()->back();
            }
            else{
                $user->delete();
            }

            if($client){
                if ($client->avatar_image) {
                    Storage::disk('public')->delete($client->avatar_image);
                }
                $client->delete();
            }
            $this->reset_custom_id();
            return redirect()->back();
        }

        public function reset_custom_id(){

            $users = User::where('role', 'client')->orderBy('created_at')->get();
            $counter = 1;
            foreach ($users as $user) {
                $user->custom_id = $counter++;
                $user->save();
            }
        }
    public function updateClient(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required','email','unique:users,email', Rule::unique('users', 'email')->ignore($id),
            'phone' => 'required|string|min:7|max:15',
            'country' => 'required|string|max:100',
        ]);

    // Update user details
    $user = User::findOrFail($id);
    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
    ]);
    $client = Client::where('user_id', $id)->first();
    if($client){
        $client->update([
            'mobile' => $validated['phone'],
            'country' => $validated['country'] ,
            'approved_by' => $request->approved_by,
        ]);}
        return redirect()->back();
}
        //=======================================================================================
    /**
     * Display a list of pending clients.
     */
    public function pendingClients()
    {
        // Get pagination parameters from the request
        $page = Request()->query('page', 1); // Default to page 1
        $pageSize = Request()->query('pageSize', 5); // Default to 7 rows per page

        // Fetch paginated clients with 'pending' status
        $clients = Client::where('status', 'pending')
            ->with('user')
            ->paginate($pageSize, ['*'], 'page', $page);

        return Inertia::render('Receptionist/pendingClients', [
            'clients' => $clients->items(), // Pass only the paginated items
            'pagination' => [
                'page' => $clients->currentPage(),
                'pageSize' => $clients->perPage(),
                'total' => $clients->total(),
            ],
        ]);
    }

    /**
     * Display a list of clients approved by the logged-in receptionist.
     */
    public function approvedClients()
    {
        $user = Auth::user(); // Get the logged-in receptionist

        // Get pagination parameters from the request
        $page = Request()->query('page', 1); // Default to page 1
        $pageSize = Request()->query('pageSize', 5); // Default to 9 rows per page


        // Fetch paginated clients approved by the logged-in receptionist
        $clients = Client::where('approved_by', $user->id)
            ->where('status', 'approved')
            ->with('user')
            ->paginate($pageSize, ['*'], 'page', $page);

        return Inertia::render('Receptionist/approvedClients', [
            'clients' => $clients->items(), // Pass only the paginated items
            'pagination' => [
                'page' => $clients->currentPage(),
                'pageSize' => $clients->perPage(),
                'total' => $clients->total(),
            ],
        ]);
    }


    /**
     * Approve a client (update the status to approved).
     */
    public function update($id)
    {
        $user = Auth::user();
        $client = Client::findOrFail($id);

        $client->update([
            'status' => 'approved',
            'approved_by' => $user->id
        ]);

        $client->user->notify(new ClientApprovedNotification());

        return redirect()->route('receptionist.pendingClients')
            ->with('success', 'Client approved successfully.  Email sent.');
    }

    /**
     * Unapprove a client (delete from database).
     */
    public function delete($id)
    {
        $client = Client::findOrFail($id);
        $userId = $client->user_id; // Get the associated user ID
        $client->delete(); // Delete the client from the clients table
        $user = User::find($userId);
        if ($user) {
            $user->delete(); // Delete the user from the users table
        }

        return redirect()->route('receptionist.pendingClients')
            ->with('success', 'Client unapproved and removed.');
    }
}
