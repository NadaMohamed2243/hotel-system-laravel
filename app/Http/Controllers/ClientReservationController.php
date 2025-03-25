<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;

class ClientReservationController extends Controller
{
    public function index()
    {

        // Get the logged-in receptionist's ID
        $receptionistId = Auth::id();

        // Fetch reservations for clients approved by this receptionist
        $reservations = Reservation::whereHas('client', function ($query) use ($receptionistId) {
            $query->where('approved_by', $receptionistId);
        })->with(['client.user', 'room'])->paginate(10); // Load relations & paginate

        // Debugging (Uncomment if needed)
        // dd($reservations);
        // dd($reservations->toArray());


        // Return reservations to Inertia
        return Inertia::render('Receptionist/clientReservations/index', [
            'reservations' => $reservations
        ]);
    }

}
