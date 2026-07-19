<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id', 'room_type_id', 'room_number', 'name', 'floor',
        'description', 'facilities', 'photos',
        'price_daily', 'price_weekly', 'price_monthly', 'price_quarterly', 'price_yearly',
        'size_sqm', 'status', 'last_cleaned_at', 'notes', 'is_active',
    ];

    protected $casts = [
        'facilities'       => 'array',
        'photos'           => 'array',
        'price_daily'      => 'float',
        'price_weekly'     => 'float',
        'price_monthly'    => 'float',
        'price_quarterly'  => 'float',
        'price_yearly'     => 'float',
        'size_sqm'         => 'float',
        'is_active'        => 'boolean',
        'last_cleaned_at'  => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function getEffectivePriceDailyAttribute(): float
    {
        return $this->price_daily ?? $this->roomType?->base_price_daily ?? 0;
    }

    public function getEffectivePriceWeeklyAttribute(): float
    {
        return $this->price_weekly ?? $this->roomType?->base_price_weekly ?? 0;
    }

    public function getEffectivePriceMonthlyAttribute(): float
    {
        return $this->price_monthly ?? $this->roomType?->base_price_monthly ?? 0;
    }

    public function getEffectivePriceQuarterlyAttribute(): float
    {
        return $this->price_quarterly ?? $this->roomType?->base_price_quarterly ?? 0;
    }

    public function getEffectivePriceYearlyAttribute(): float
    {
        return $this->price_yearly ?? $this->roomType?->base_price_yearly ?? 0;
    }

    // Deskripsi efektif: pakai deskripsi kamar jika ada, fallback ke tipe kamar
    public function getEffectiveDescriptionAttribute(): ?string
    {
        return $this->description ?? $this->roomType?->description;
    }

    // Fasilitas efektif: merge fasilitas tipe + override kamar
    public function getEffectiveFacilitiesAttribute(): array
    {
        $typeFacilities = $this->roomType?->facilities ?? [];
        $roomFacilities = $this->facilities ?? [];
        return array_unique(array_merge($typeFacilities, $roomFacilities));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'success',
            'occupied'    => 'danger',
            'maintenance' => 'warning',
            'reserved'    => 'info',
            default       => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'Tersedia',
            'occupied'    => 'Terisi',
            'maintenance' => 'Maintenance',
            'reserved'    => 'Dipesan',
            default       => $this->status,
        };
    }
}
