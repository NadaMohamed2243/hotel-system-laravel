<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class Client extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'approved_by',
        'status',
        'mobile',
        'country',
        'gender',
        'mobile', 
        'last_login_at'
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    // Relationship with User (Client belongs to a user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with Receptionist (approved_by references a receptionist)
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by'); // approved_by is a user_id of a receptionist
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
