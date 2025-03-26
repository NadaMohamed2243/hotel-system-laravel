<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'number',
        'capacity',
        'price',
        'floor_id',
        'manager_id',
        'is_reserved'
    ];

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function getPriceInDollarsAttribute()
    {
        return number_format($this->price / 100, 2);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
