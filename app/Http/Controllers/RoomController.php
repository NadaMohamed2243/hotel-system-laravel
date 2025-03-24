<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class RoomController extends Controller
{
    public function index()
    {
        $user=auth()->user();
        // Check permission manually rather than in middleware to avoid errors
        try {
            if (!$user->hasPermissionTo('manage managers')) {
                return abort(403, 'You do not have permission to view managers.');
            }
        } catch (\Exception $e) {
            Log::warning('Permission check failed: ' . $e->getMessage());
            // Fallback to role check if permission check fails - only admin can see managers
            if ($user->role !== 'admin') {
                return abort(403, 'Unauthorized action.');
            }
        }

        $rooms = Room::with('manager')->get()
            ->map(function ($room)use($user) {
                return [
                    'id' => $room->id,
                    'number' => $room->number,
                    'capacity' => $room->capacity,
                    'price' => $room->price_in_dollars, // Convert to dollars
                    // 'floor' => $room->floor->name,
                    'manager' => $user->role === 'admin' ? $room->manager->name : null,
                    'canEdit' => $user->role === 'admin' || $room->manager_id === $user->id
                ];
            });

            $managers = User::where('role', 'manager')->get();

            return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
            'managers'=>$managers,
            'role'=>$user->role
        ]);
    }

}
