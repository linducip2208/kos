<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRequest extends Model
{
    use HasFactory;

    /** Funnel CRM lengkap. */
    public const STAGES = [
        'new_lead'           => 'Lead Baru',
        'contacted'          => 'Dihubungi',
        'viewing_scheduled'  => 'Jadwal Survey',
        'interested'         => 'Tertarik',
        'reserved'           => 'Reserved',
        'deposit_pending'    => 'Deposit Pending',
        'deposit_paid'       => 'Deposit Dibayar',
        'contract'           => 'Kontrak',
        'move_in'            => 'Move-in',
        'converted'          => 'Converted',
        'lost'               => 'Gagal',
    ];

    public const SOURCES = [
        'website'     => 'Website',
        'whatsapp'    => 'WhatsApp',
        'instagram'   => 'Instagram',
        'tiktok'      => 'TikTok',
        'facebook'    => 'Facebook',
        'marketplace' => 'Marketplace',
        'referral'    => 'Referral',
        'walk_in'     => 'Walk-in',
        'other'       => 'Lainnya',
    ];

    protected $fillable = [
        'property_id', 'room_id', 'room_type_id',
        'name', 'email', 'phone', 'whatsapp',
        'desired_move_in', 'billing_cycle', 'budget',
        'message', 'status', 'stage', 'source', 'campaign',
        'assigned_to', 'follow_up_date', 'lost_reason',
        'admin_notes', 'converted_to_lease_id', 'converted_to_occupant_id',
        'converted_at',
    ];

    protected $casts = [
        'desired_move_in' => 'date',
        'follow_up_date'  => 'date',
        'budget'          => 'decimal:2',
        'converted_at'    => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function roomType(): BelongsTo { return $this->belongsTo(RoomType::class); }
    public function convertedLease(): BelongsTo { return $this->belongsTo(Lease::class, 'converted_to_lease_id'); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class)->orderByDesc('created_at');
    }

    // ── Stage helpers ────────────────────────────────────────────────────

    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? $this->stage;
    }

    public function getStageColorAttribute(): string
    {
        return match ($this->stage) {
            'new_lead'          => 'gray',
            'contacted'         => 'info',
            'viewing_scheduled' => 'info',
            'interested'        => 'purple',
            'reserved'          => 'warning',
            'deposit_pending'   => 'warning',
            'deposit_paid'      => 'success',
            'contract'          => 'success',
            'move_in', 'converted' => 'success',
            'lost'              => 'danger',
            default             => 'gray',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ($this->source ?? '-');
    }

    public function isWon(): bool
    {
        return in_array($this->stage, ['move_in', 'converted'], true)
            || $this->status === 'converted';
    }

    public function isLost(): bool
    {
        return $this->stage === 'lost' || $this->status === 'rejected';
    }

    public function needsFollowUp(): bool
    {
        return !$this->isWon()
            && !$this->isLost()
            && $this->follow_up_date !== null
            && $this->follow_up_date->lte(today());
    }

    // ── Legacy status labels (backward compat) ───────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu', 'contacted' => 'Dihubungi',
            'approved'  => 'Disetujui','rejected'  => 'Ditolak',
            'converted' => 'Jadi Penyewa', default => $this->status,
        };
    }
}
