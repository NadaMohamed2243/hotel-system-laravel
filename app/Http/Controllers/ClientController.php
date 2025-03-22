<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function pendingClients()
    {
        $clients = Client::where('status', 'pending')->get();

        return Inertia::render('Receptionist/pendingClients', [
            'clients' => $clients
        ]);
    }

    public function approvedClients()
    {
        @dd(Auth::user());
        // $clients = Client::where('status', 'approved')->get();
        $user = Request()->user(); // Get the logged-in user

        $clients = Client::where('approved_by', $user->id) // Get only clients approved by the logged receptionist
            ->where('status', 'approved') // Ensure they are approved
            ->get();

        return Inertia::render('Receptionist/approvedClients', [
            'clients' => $clients
        ]);
    }

    public function clientReservations()
    {

        return Inertia::render('Receptionist/clientReservations');
    }

    public function approveClient($id)
    {
        return 'appeoveClient';
    }
}
