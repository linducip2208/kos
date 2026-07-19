<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id', 'room_id', 'room_type_id',
        'name', 'email', 'phone', 'whatsapp',
        'desired_move_in', 'billing_cycle',
        'message', 'status', 'admin_notes',
        'converted_to_lease_id', 'converted_to_occupant_id',
    ];

    protected $casts = [
        'desired_move_in' => 'date',
    ];

    public function property(): BelongsTo      { return $this->belongsTo(Property::class); }
    public function room(): BelongsTo          { return $this->belongsTo(Room::class); }
    public function roomType(): BelongsTo      { return $this->belongsTo(RoomType::class); }
    public function convertedLease(): BelongsTo { return $this->belongsTo(Lease::class, 'converted_to_lease_id'); }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning', 'contacted' => 'info',
            'approved'  => 'success', 'rejected'  => 'danger',
            'converted' => 'gray',    default      => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu', 'contacted' => 'Dihubungi',
            'approved'  => 'Disetujui','rejected'  => 'Ditolak',
            'converted' => 'Jadi Penyewa', default => $this->status,
        };
    }
}
