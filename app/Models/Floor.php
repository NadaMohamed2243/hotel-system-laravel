<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'description',
        'manager_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($floor) {
            $floor->number = static::generateUniqueNumber();
            // Automatically assign current user as manager if not admin
            if (!isset($floor->manager_id) && Auth::check()) {
                $floor->manager_id = Auth::id();
            }
        });

        static::updating(function ($floor) {
            // Prevent number modification after creation
            if ($floor->isDirty('number')) {
                $floor->number = $floor->getOriginal('number');
            }
        });
    }

    private static function generateUniqueNumber(): string
    {
        do {
            $number = (string) mt_rand(1000, 9999); // 4-digit number
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // public function rooms(): HasMany
    // {
    //     return $this->hasMany(Room::class);
    // }

    // public function canBeDeleted(): bool
    // {
    //     return !$this->rooms()->exists();
    // }
}