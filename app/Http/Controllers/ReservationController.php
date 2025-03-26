<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'page' => 'sometimes|integer|min:1',
                'pageSize' => 'sometimes|integer|min:1|max:100',
            ]);

            $page = $validated['page'] ?? 1;
            $pageSize = $validated['pageSize'] ?? 5;

            $reservations = Reservation::whereHas('client')
                ->with(['client.user:id,name,email', 'room:id,number'])
                ->orderBy('created_at', 'desc')
                ->paginate($pageSize, ['*'], 'page', $page);

            return Inertia::render('Managers/clientReservations/index', [
                'reservations' => $reservations->items(),
                'pagination' => [ // Make sure this matches your frontend expectation
                    'page' => $reservations->currentPage(),
                    'pageSize' => $reservations->perPage(),
                    'total' => $reservations->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('ClientReservationController error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load reservations');
        }
    }
}
