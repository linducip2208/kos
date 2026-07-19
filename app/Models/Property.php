<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'address', 'city', 'province', 'postal_code',
        'latitude', 'longitude', 'description', 'facilities',
        'photos', 'rules', 'is_active',
    ];

    protected $casts = [
        'facilities' => 'array',
        'photos'     => 'array',
        'is_active'  => 'boolean',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
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
        if ($total === 0) return 0;
        $occupied = $this->rooms()->where('status', 'occupied')->count();
        return round(($occupied / $total) * 100, 1);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
