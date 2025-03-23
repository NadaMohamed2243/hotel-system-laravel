<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a list of pending clients.
     */
    public function pendingClients()
    {
        $clients = Client::where('status', 'pending') ->with('user')->get();

        return Inertia::render('Receptionist/pendingClients', [
            'clients' => $clients
        ]);
    }

    /**
     * Display a list of clients approved by the logged-in receptionist.
     */
    public function approvedClients()
    {
        $user = Auth::user(); // Get the logged-in receptionist

        $clients = Client::where('approved_by', $user->id) // Only show clients approved by this receptionist
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Receptionist/approvedClients', [
            'clients' => $clients
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
     * Approve a client.
     */
    public function approveClient($id)
    {
        $user = Auth::user();
        $client = Client::findOrFail($id);

        $client->update([
            'status' => 'approved',
            'approved_by' => $user->id
        ]);

        return redirect()->route('receptionist.pendingClients')
            ->with('success', 'Client approved successfully.');
    }

    /**
     * Unapprove a client (delete from database).
     */
    public function unapproveClient($id)
    {
        $client = Client::findOrFail($id);
        $client->delete(); // Remove from the database

        return redirect()->route('receptionist.approvedClients')
            ->with('success', 'Client unapproved and removed.');
    }
}
