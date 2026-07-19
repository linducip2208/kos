<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Lease extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'room_id', 'occupant_id', 'lease_number', 'start_date', 'end_date',
        'price', 'deposit', 'deposit_returned', 'deposit_returned_at',
        'billing_cycle', 'billing_date', 'status', 'terminated_at',
        'termination_reason', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'terminated_at'       => 'date',
        'deposit_returned_at' => 'date',
        'price'               => 'float',
        'deposit'             => 'float',
        'deposit_returned'    => 'float',
    ];

    public function room(): BelongsTo     { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo { return $this->belongsTo(Occupant::class); }
    public function invoices(): HasMany   { return $this->hasMany(Invoice::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function checklists(): HasMany   { return $this->hasMany(RoomChecklist::class); }
    public function eContract(): HasOne    { return $this->hasOne(EContract::class); }
    public function utilityReadings(): HasMany { return $this->hasMany(UtilityReading::class); }

    public function getDaysUntilExpiryAttribute(): int
    {
        return Carbon::today()->diffInDays($this->end_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_until_expiry <= 30 && $this->days_until_expiry >= 0;
    }

    protected static function booted(): void
    {
        static::saved(function (Lease $lease) {
            if ($lease->status === 'active') {
                $lease->room()->update(['status' => 'occupied']);
            } elseif (in_array($lease->status, ['expired', 'terminated'])) {
                $lease->room()->update(['status' => 'available']);
            }
        });
    }
}
