<?php

namespace App\Models;

use App\Services\RoomStatusService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Room extends Model
{
    use HasFactory;

    public const STATUSES = [
        'available'   => 'Tersedia',
        'reserved'    => 'Dipesan',
        'occupied'    => 'Terisi',
        'notice_given'=> 'Notice Given',
        'cleaning'    => 'Dibersihkan',
        'inspection'  => 'Inspeksi',
        'maintenance' => 'Maintenance',
        'blocked'     => 'Diblokir',
        'inactive'    => 'Nonaktif',
    ];

    protected $fillable = [
        'property_id', 'room_type_id', 'room_number', 'name', 'floor',
        'description', 'facilities', 'photos',
        'price_daily', 'price_weekly', 'price_monthly', 'price_quarterly', 'price_yearly',
        'size_sqm', 'status', 'last_cleaned_at', 'notes', 'blocked_reason', 'is_active',
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

    // ── Relations ────────────────────────────────────────────────────────

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
        return $this->hasOne(Lease::class)->whereIn('status', ['active', 'expiring_soon']);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(RoomInventoryItem::class);
    }

    public function checkinRecords(): HasMany
    {
        return $this->hasMany(CheckinRecord::class);
    }

    public function utilityRates(): HasMany
    {
        return $this->hasMany(UtilityRate::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function utilityReadings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
    }

    // ── Effective attributes (room overrides room type) ──────────────────

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

    public function getEffectiveDescriptionAttribute(): ?string
    {
        return $this->description ?? $this->roomType?->description;
    }

    public function getEffectiveFacilitiesAttribute(): array
    {
        $typeFacilities = $this->roomType?->facilities ?? [];
        $roomFacilities = $this->facilities ?? [];

        return array_unique(array_merge($typeFacilities, $roomFacilities));
    }

    // ── Status helpers ───────────────────────────────────────────────────

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status] ?? $status;
    }

    public function getStatusColorAttribute(): string
    {
        return RoomStatusService::color($this->status);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    public function isOccupiable(): bool
    {
        return in_array($this->status, ['available'], true)
            || ($this->status === 'cleaning'); // boleh langsung di-assign setelah cleaning selesai
    }

    /**
     * Transisi status dengan validasi workflow.
     * Lemparkan ValidationException bila transisi tidak logis.
     */
    public function transitionTo(string $newStatus, ?string $reason = null): static
    {
        app(RoomStatusService::class)->transition($this, $newStatus, $reason);

        return $this;
    }
}
