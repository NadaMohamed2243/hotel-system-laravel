<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // Add this line


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable ,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'national_id',
        'avatar_image',
        'email_verified_at',
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship with Client
    public function client()
    {
        return $this->hasOne(Client::class, 'user_id');
    }
       
    public function receptionist()
    {
        return $this->hasOne(Receptionist::class);
    }

    public function managedReceptionists()
    {
        return $this->hasMany(Receptionist::class, 'manager_id');
    }

    protected static function booted()
    {
        // static::created(function ($user) {
        //     if ($user->role === 'receptionist') {
        //         Receptionist::create([
        //             'user_id' => $user->id,
        //             'manager_id' => auth()->user()?->id ?? 1, // Default to manager ID 1 if no authenticated user
        //             'is_banned' => false,
        //         ]);
        //     }
        // });
    }

}
