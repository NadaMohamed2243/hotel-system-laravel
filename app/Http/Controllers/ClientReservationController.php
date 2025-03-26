<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientReservationController extends Controller
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
            $receptionistId = Auth::id();

            $reservations = Reservation::whereHas('client', function ($query) use ($receptionistId) {
                $query->where('approved_by', $receptionistId);
            })
                ->with(['client.user:id,name,email', 'room:id,number'])
                ->orderBy('created_at', 'desc')
                ->paginate($pageSize, ['*'], 'page', $page);

            return Inertia::render('Receptionist/clientReservations/index', [
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
