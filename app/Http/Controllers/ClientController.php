<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function index(){
        $user = auth()->user();
        $clients = User::where('role', 'client')
        ->with('client') // Assuming a one-to-one relationship between User and Client
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->client->mobile ?? 'N/A',
                'approved_by' => $user->client->approved_by ?? 'N/A',
                'gender' => $user->client->gender ?? 'N/A',
                'status' => $user->client->status ?? 'N/A',
                'country' => $user->client->country ?? 'N/A',
            ];
        });
        // dd("hello");
        // dd($clients);
        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }
    /**
     * Display a list of pending clients.
     */
    public function pendingClients()
    {
        // Get pagination parameters from the request
        $page = Request()->query('page', 1); // Default to page 1
        $pageSize = Request()->query('pageSize', 7); // Default to 7 rows per page

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
        $pageSize = Request()->query('pageSize', 8); // Default to 9 rows per page


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
     * Display reservations of clients approved by the logged-in receptionist.
     */
    public function clientReservations()
    {
        // $user = Auth::user();

        // $clients = Client::where('approved_by', $user->id)
        //     ->where('status', 'approved')
        //     ->with('reservations') // Assuming the Client model has a `reservations` relationship
        //     ->get();

        // return Inertia::render('Receptionist/clientReservations', [
        //     'clients' => $clients
        // ]);

        return Inertia::render('Receptionist/clientReservations');
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
