<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Models\User;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Requests\Rooms\StoreRoomRequest;

class RoomController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $this->reset_custom_id();
        //retrieving data
        $rooms = Room::with('manager')->get()
            ->map(function ($room)use($user) {
                return [
                    'id' => $room->id,
                    'custom_id' => $room->custom_id,
                    'number' => $room->number,
                    'capacity' => $room->capacity,
                    'price' => $room->price_in_dollars, // Convert to dollars
                    // 'floor' => $room->floor->name,
                    'manager' => ($user->role === 'admin' && $room->manager) ? $room->manager->name : 'Admin',
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

    public function store(Request $request)
    {
    // Validate input data
    $validatedData = $request->validate([
        'number' => ['required', 'string', 'digits_between:4,10', 'unique:rooms,number'],
        'capacity' => ['required', 'integer','min:1'],
        'price' => ['required', 'numeric'],
    ]);

    //Convert price to cent
    $validatedData['price'] = (int) ($validatedData['price'] * 100);
    // Automatically assign manager_id if user is a manager
    $validatedData['manager_id'] = $user->role === 'manager' ? $user->id : ($request->manager_id ?? null);
    $this->reset_custom_id();

    // Store the room
    Room::create($validatedData);
    return redirect()->route('rooms.index');
    }

    public function update(Request $request,Room $room){
        $validatedData = $request->validate([
            'capacity' => ['required', 'integer','min:1'],
            'price' => ['required', 'numeric'],
            'manager_id' => ['nullable', 'exists:users,id'], // Ensure the manager exists
        ]);

         //Convert price to cent
        $validatedData['price'] = (int) ($validatedData['price'] * 100);

        $room->update($validatedData);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy($id){
        $room = Room::find($id);
        if ($room && $room->is_reserved == 0){
            $room->delete();
            $this->reset_custom_id();
            return redirect()->route('rooms.index');
        }
        else{
            return redirect()->route('rooms.index')->with('error', 'Room is reserved.');
        }
    }
    //to update the order of showing id
    public function reset_custom_id(){
        Room::orderBy('id')->get()->each(function ($room, $index) {
            $room->custom_id = $index + 1;
            $room->save();
        });
    }
    //to update is_reserved column
    public function reserve(Request $request, Room $room)
    {
        $request->validate([
            'is_reserved' => ['required', 'boolean'],
        ]);
        $room->update(['is_reserved' => $request->is_reserved]);
        return back();
    }
}
