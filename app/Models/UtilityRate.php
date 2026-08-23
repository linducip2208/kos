<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityRate extends Model
{
    public const SCOPES = [
        'global'    => 'Global',
        'property'  => 'Properti',
        'room_type' => 'Tipe Kamar',
        'room'      => 'Kamar',
    ];

    public const UTILITY_TYPES = [
        'electricity' => 'Listrik',
        'water'       => 'Air',
    ];

    protected $fillable = [
        'scope', 'property_id', 'room_type_id', 'room_id', 'utility_type',
        'rate_per_unit', 'fixed_charge', 'admin_charge',
        'minimum_charge', 'minimum_usage', 'effective_from', 'is_active',
    ];

    protected $casts = [
        'rate_per_unit'  => 'decimal:4',
        'fixed_charge'   => 'decimal:2',
        'admin_charge'   => 'decimal:2',
        'minimum_charge' => 'decimal:2',
        'minimum_usage'  => 'decimal:2',
        'effective_from' => 'date',
        'is_active'      => 'boolean',
    ];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function roomType(): BelongsTo { return $this->belongsTo(RoomType::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->scope) {
            'global'   => 'Global',
            'property' => $this->property?->name ?? 'Properti',
            'room_type'=> ($this->roomType?->name ?? 'Tipe'),
            'room'     => $this->room?->room_number ?? 'Kamar',
            default    => $this->scope,
        };
    }
}
