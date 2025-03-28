<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Floor;
use App\Http\Resources\RoomResource;

class RoomController extends Controller
{
    public function index(){
        $rooms = Room::with('floor')->get();
        foreach($rooms as $room){
            $room->price=$room->price_in_dollars;
        }
        return RoomResource::collection($rooms);
    }
    public function show($id)
    {
        $room=Room::with('floor')->findOrFail($id);
        return new  RoomResource($room);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'capacity' => ['required', 'integer','min:1'],
            'price' => ['required', 'numeric'],
            'floor_id'=>['required','exists:floors,id']
        ]);
        $lastRoom = Room::where('floor_id', $request->floor_id)->latest('number')->first();
        $newRoomNumber = $lastRoom ? $lastRoom->number + 1 : ($request->floor_id * 1000 + 1);
        //Convert price to cent
        $validatedData['price'] = (int) ($validatedData['price'] * 100);
        $validatedData['number']=$newRoomNumber;

        $room=Room::create($validatedData);

        return new RoomResource($room);
    }
}
