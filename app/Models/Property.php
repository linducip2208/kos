<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'address', 'city', 'province', 'postal_code',
        'latitude', 'longitude', 'description', 'facilities',
        'photos', 'rules', 'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $property) {
            if (blank($property->slug)) {
                $base = Str::slug($property->name) ?: 'properti';
                $slug = $base;
                $i = 2;
                while (static::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $property->slug = $slug;
            }
        });

        static::updating(function (Property $property) {
            if (blank($property->slug)) {
                $base = Str::slug($property->name) ?: 'properti';
                $slug = $base;
                $i = 2;
                while (static::withTrashed()->where('slug', $slug)->whereKeyNot($property->getKey())->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $property->slug = $slug;
            }
        });
    }

    protected $casts = [
        'facilities' => 'array',
        'photos' => 'array',
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function leases(): HasManyThrough
    {
        return $this->hasManyThrough(Lease::class, Room::class);
    }

    /** Property manager yang ditugaskan ke properti ini. */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function availableRooms(): HasMany
    {
        return $this->hasMany(Room::class)->where('status', 'available')->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getOccupancyRateAttribute(): float
    {
        $total = $this->rooms()->where('is_active', true)->count();
        if ($total === 0) {
            return 0;
        }
        $occupied = $this->rooms()->where('status', 'occupied')->count();

        return round(($occupied / $total) * 100, 1);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
