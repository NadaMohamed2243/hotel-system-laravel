<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function myReservations()
    {
        $user = Auth::user();

        $reservations = Reservation::where('client_id', $user->client->id)
            ->select('id', 'room_number', 'accompany_number', 'paid_price')
            ->get();

        return response()->json(['reservations' => $reservations]);
    }
}
