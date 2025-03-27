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
        $room = Room::create([
            'number' => $request->number,
            'capacity' => $request->capacity,
            'price'=>(int) (($request->price)*100),
            'is_reserved'=>$request->is_reserved,
            'floor_id' => $request->floor_id,
        ]);

        return new RoomResource($room);
    }
}
