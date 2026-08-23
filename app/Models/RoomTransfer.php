<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTransfer extends Model
{
    protected $fillable = [
        'lease_id', 'from_room_id', 'to_room_id', 'effective_date',
        'prorate_amount', 'transfer_deposit', 'final_utility_done',
        'inspection_required', 'inspection_completed_at', 'status',
        'notes', 'performed_by',
    ];

    protected $casts = [
        'effective_date'          => 'date',
        'inspection_completed_at' => 'datetime',
        'prorate_amount'          => 'decimal:2',
        'transfer_deposit'        => 'boolean',
        'final_utility_done'      => 'boolean',
        'inspection_required'     => 'boolean',
    ];

    public function lease(): BelongsTo { return $this->belongsTo(Lease::class); }
    public function fromRoom(): BelongsTo { return $this->belongsTo(Room::class, 'from_room_id'); }
    public function toRoom(): BelongsTo { return $this->belongsTo(Room::class, 'to_room_id'); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }

    public function getProrateLabelAttribute(): string
    {
        if ((float) $this->prorate_amount === 0.0) {
            return 'Tidak ada selisih';
        }

        return ($this->prorate_amount > 0 ? 'Tenant bayar ' : 'Kredit tenant ')
            .'Rp '.number_format(abs((float) $this->prorate_amount), 0, ',', '.');
    }
}
