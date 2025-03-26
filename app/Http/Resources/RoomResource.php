<?php

namespace App\Http\Resources;
use App\Http\Resources\FloorResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'number' => $this->number,
                'capacity' => $this->capacity,
                'price'=>$this->price,
                'is_reserved'=>$this->is_reserved,
                'floor' => new FloorResource($this->floor),
        ];
    }
}
