<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        /**
     * Define the relationship with the Receptionist model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function receptionist()
    {
        return $this->hasOne(Receptionist::class);
    }

    /**
     * Define the relationship with Receptionists managed by this user (Manager).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function managedReceptionists()
    {
        return $this->hasMany(Receptionist::class, 'manager_id');
    }
}
