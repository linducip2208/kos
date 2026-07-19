<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id', 'name', 'description', 'size_sqm',
        'base_price_daily', 'base_price_weekly',
        'base_price_monthly', 'base_price_quarterly', 'base_price_yearly',
        'facilities', 'photos', 'max_occupants',
    ];

    protected $casts = [
        'facilities'            => 'array',
        'photos'                => 'array',
        'base_price_daily'      => 'float',
        'base_price_weekly'     => 'float',
        'base_price_monthly'    => 'float',
        'base_price_quarterly'  => 'float',
        'base_price_yearly'     => 'float',
        'size_sqm'              => 'float',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
