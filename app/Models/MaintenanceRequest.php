<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;

    public const STATUSES = [
        'open'                => 'Terbuka',
        'triage'              => 'Triage',
        'assigned'            => 'Ditugaskan',
        'in_progress'         => 'Dikerjakan',
        'waiting_parts'       => 'Tunggu Part',
        'completed'           => 'Selesai',
        'tenant_confirmation' => 'Konfirmasi Tenant',
        'closed'              => 'Ditutup',
        'cancelled'           => 'Dibatalkan',
    ];

    public const PRIORITIES = [
        'low'    => 'Rendah',
        'medium' => 'Sedang',
        'high'   => 'Tinggi',
        'urgent' => 'Urgent',
    ];

    public const CATEGORIES = [
        'electrical' => 'Kelistrikan',
        'plumbing'   => 'Perairan',
        'ac'         => 'AC',
        'furniture'  => 'Furniture',
        'structure'  => 'Struktur/Bangunan',
        'cleaning'   => 'Kebersihan',
        'appliance'  => 'Peralatan',
        'security'   => 'Keamanan',
        'general'    => 'Umum',
    ];

    /** SLA jam berdasarkan priority. */
    public const SLA_HOURS = [
        'urgent' => 4,
        'high'   => 24,
        'medium' => 72,
        'low'    => 168,
    ];

    protected $fillable = [
        'room_id', 'occupant_id', 'assigned_to', 'vendor_id',
        'title', 'category', 'description', 'photos', 'before_photos', 'after_photos',
        'priority', 'status', 'sla_hours', 'sla_due_at',
        'estimated_cost', 'actual_cost', 'materials',
        'resolution_notes', 'resolution_photos', 'internal_notes',
        'tenant_rating', 'tenant_feedback',
        'completed_at', 'closed_at', 'resolved_at',
    ];

    protected $casts = [
        'photos'            => 'array',
        'before_photos'     => 'array',
        'after_photos'      => 'array',
        'materials'         => 'array',
        'resolution_photos' => 'array',
        'estimated_cost'    => 'float',
        'actual_cost'       => 'float',
        'resolved_at'       => 'datetime',
        'completed_at'      => 'datetime',
        'closed_at'         => 'datetime',
        'sla_due_at'        => 'datetime',
        'tenant_rating'     => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo { return $this->belongsTo(Occupant::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    // ── Labels ───────────────────────────────────────────────────────────

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'danger', 'high' => 'warning',
            'medium' => 'info',   'low'  => 'success', default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'                => 'danger',
            'triage'              => 'gray',
            'assigned'            => 'info',
            'in_progress'         => 'warning',
            'waiting_parts'       => 'purple',
            'completed', 'resolved' => 'success',
            'tenant_confirmation' => 'amber',
            'closed'              => 'slate',
            'cancelled'           => 'slate',
            default               => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        // 'resolved' = alias legacy dari 'completed'
        return self::STATUSES[$this->status === 'resolved' ? 'completed' : $this->status] ?? $this->status;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    // ── SLA ──────────────────────────────────────────────────────────────

    public function isOverdueSla(): bool
    {
        return $this->sla_due_at !== null
            && !in_array($this->status, ['completed', 'closed', 'cancelled'], true)
            && Carbon::now()->gt($this->sla_due_at);
    }

    public function getSlaBadgeAttribute(): ?string
    {
        if (!$this->sla_due_at || in_array($this->status, ['completed', 'closed', 'cancelled'], true)) {
            return null;
        }

        return Carbon::now()->gt($this->sla_due_at)
            ? 'SLA terlampaui'
            : 'SLA '.$this->sla_due_at->diffForHumans(short: true);
    }

    /** Biaya final: actual, fallback estimated. */
    public function getFinalCostAttribute(): float
    {
        return (float) ($this->actual_cost ?? $this->estimated_cost ?? 0);
    }
}
