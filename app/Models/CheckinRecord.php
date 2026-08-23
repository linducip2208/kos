<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinRecord extends Model
{
    public const TYPES = [
        'check_in'  => 'Check-in',
        'check_out' => 'Check-out',
    ];

    protected $fillable = [
        'lease_id', 'room_id', 'occupant_id', 'type',
        'meter_electric_prev', 'meter_electric_current',
        'meter_water_prev', 'meter_water_current',
        'checklist', 'photos', 'missing_items', 'key_handover',
        'damage_amount', 'cleaning_amount', 'unpaid_utility', 'deposit_deduction',
        'settlement', 'tenant_payable',
        'acknowledged_by', 'acknowledgement_signature', 'acknowledged_at',
        'performed_by', 'completed_at',
    ];

    protected $casts = [
        'checklist'     => 'array',
        'photos'        => 'array',
        'missing_items' => 'array',
        'settlement'    => 'array',
        'key_handover'  => 'boolean',
        'damage_amount' => 'decimal:2',
        'cleaning_amount'=> 'decimal:2',
        'unpaid_utility'=> 'decimal:2',
        'deposit_deduction' => 'decimal:2',
        'tenant_payable' => 'decimal:2',
        'meter_electric_prev' => 'decimal:2',
        'meter_electric_current' => 'decimal:2',
        'meter_water_prev' => 'decimal:2',
        'meter_water_current' => 'decimal:2',
        'acknowledged_at'=> 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function lease(): BelongsTo { return $this->belongsTo(Lease::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo { return $this->belongsTo(Occupant::class); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getElectricUsageAttribute(): ?float
    {
        if ($this->meter_electric_current === null) {
            return null;
        }

        return max(0, (float) $this->meter_electric_current - (float) ($this->meter_electric_prev ?? 0));
    }

    public function getWaterUsageAttribute(): ?float
    {
        if ($this->meter_water_current === null) {
            return null;
        }

        return max(0, (float) $this->meter_water_current - (float) ($this->meter_water_prev ?? 0));
    }
}
