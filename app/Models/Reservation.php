<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'room_id',
        'accompany_number',
        'paid_price',
        'reserved_at'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

     // Function to get price in dollars
     public function getFormattedPriceAttribute()
     {
         return number_format($this->paid_price / 100, 2);
     }
}
