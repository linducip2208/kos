<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityReading extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_id', 'lease_id', 'type',
        'previous_reading', 'current_reading',
        'rate_per_unit', 'amount',
        'reading_date', 'billing_period',
        'photo', 'added_to_invoice', 'invoice_id',
    ];

    protected $casts = [
        'previous_reading' => 'float',
        'current_reading'  => 'float',
        'rate_per_unit'    => 'float',
        'amount'           => 'float',
        'reading_date'     => 'date',
        'billing_period'   => 'date',
        'added_to_invoice' => 'boolean',
    ];

    public function room(): BelongsTo    { return $this->belongsTo(Room::class); }
    public function lease(): BelongsTo   { return $this->belongsTo(Lease::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }

    public function getUsageAttribute(): float
    {
        return $this->current_reading - $this->previous_reading;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'electricity' => 'Listrik', 'water' => 'Air', 'gas' => 'Gas', default => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'electricity' => 'heroicon-o-bolt',
            'water'       => 'heroicon-o-beaker',
            'gas'         => 'heroicon-o-fire',
            default       => 'heroicon-o-squares-2x2',
        };
    }
}
