<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomInventoryItem extends Model
{
    public const CONDITIONS = [
        'good'     => 'Baik',
        'fair'     => 'Cukup',
        'poor'     => 'Kurang',
        'broken'   => 'Rusak',
        'replaced' => 'Diganti',
    ];

    protected $fillable = [
        'room_id', 'name', 'category', 'serial_number', 'quantity',
        'acquired_at', 'condition', 'replacement_value', 'photo', 'notes',
    ];

    protected $casts = [
        'acquired_at'       => 'date',
        'replacement_value' => 'decimal:2',
        'quantity'          => 'integer',
    ];

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }

    public function getConditionLabelAttribute(): string
    {
        return self::CONDITIONS[$this->condition] ?? $this->condition;
    }

    public function getConditionColorAttribute(): string
    {
        return match ($this->condition) {
            'good'   => 'success',
            'fair'   => 'info',
            'poor'   => 'warning',
            'broken' => 'danger',
            default  => 'gray',
        };
    }
}
