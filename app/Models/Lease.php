<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lease extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft'             => 'Draft',
        'pending_approval'  => 'Menunggu Persetujuan',
        'awaiting_signature'=> 'Menunggu TTD Tenant',
        'active'            => 'Aktif',
        'expiring_soon'     => 'Segera Berakhir',
        'renewed'           => 'Diperbarui',
        'ended'             => 'Berakhir',
        'expired'           => 'Berakhir',   // legacy alias
        'terminated'        => 'Terminasi',
        'cancelled'         => 'Dibatalkan',
        'pending'           => 'Pending',    // legacy alias draft
    ];

    protected $fillable = [
        'room_id', 'occupant_id', 'lease_number',
        'renewed_from_lease_id', 'start_date', 'end_date',
        'price', 'deposit', 'deposit_returned', 'deposit_returned_at',
        'billing_cycle', 'billing_date', 'status',
        'approved_by', 'approved_at', 'tenant_signed_at', 'owner_signed_at',
        'terminated_at', 'termination_reason', 'notice_given_at', 'moved_out_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'terminated_at'       => 'date',
        'moved_out_at'        => 'date',
        'notice_given_at'     => 'date',
        'deposit_returned_at' => 'date',
        'approved_at'         => 'datetime',
        'tenant_signed_at'    => 'datetime',
        'owner_signed_at'     => 'datetime',
        'price'               => 'float',
        'deposit'             => 'float',
        'deposit_returned'    => 'float',
    ];

    // ── Relations ────────────────────────────────────────────────────────

    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo { return $this->belongsTo(Occupant::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function checklists(): HasMany { return $this->hasMany(RoomChecklist::class); }
    public function eContract(): HasOne { return $this->hasOne(EContract::class); }
    public function utilityReadings(): HasMany { return $this->hasMany(UtilityReading::class); }
    public function transfers(): HasMany { return $this->hasMany(RoomTransfer::class); }
    public function checkinRecords(): HasMany { return $this->hasMany(CheckinRecord::class); }
    public function deposits(): HasMany { return $this->hasMany(Deposit::class); }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_lease_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'renewed_from_lease_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status] ?? $status;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabel($this->status);
    }

    /** Status operasional: aktif termasuk yang segera berakhir. */
    public function isOperational(): bool
    {
        return in_array($this->status, ['active', 'expiring_soon'], true);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isOperational();
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        if (!$this->end_date) {
            return PHP_INT_MAX;
        }

        return Carbon::today()->diffInDays($this->end_date, false);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->days_until_expiry <= 30 && $this->days_until_expiry >= 0 && $this->isOperational();
    }

    /** Total terbayar dari semua invoice (via payment records). */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->invoices()->withSum('verifiedPayments as paid_sum', 'amount')
            ->get()->sum('paid_sum');
    }

    protected static function booted(): void
    {
        static::saving(function (Lease $lease) {
            // Normalisasi legacy alias
            if ($lease->status === 'pending') {
                $lease->status = 'draft';
            }

            // expiring_soon dihitung otomatis dari tanggal berakhir.
            if ($lease->status === 'active'
                && $lease->end_date
                && $lease->days_until_expiry <= 30
                && $lease->days_until_expiry >= 0) {
                $lease->status = 'expiring_soon';
            } elseif ($lease->status === 'expiring_soon'
                && $lease->end_date
                && ($lease->days_until_expiry > 30 || $lease->days_until_expiry < 0)) {
                $lease->status = 'active';
            }
        });

        static::saved(function (Lease $lease) {
            if ($lease->wasChanged(['status', 'room_id']) || $lease->wasRecentlyCreated) {
                $lease->syncRoomStatus();
            }

            // Lease berakhir → kamar masuk tahap inspeksi/cleaning
            if ($lease->wasChanged('status')
                && in_array($lease->status, ['ended', 'expired', 'terminated'], true)
                && !in_array($lease->getOriginal('status'), ['ended', 'expired', 'terminated'], true)) {
                $room = $lease->room;
                if ($room && in_array($room->status, ['occupied', 'notice_given'], true)) {
                    try {
                        $room->transitionTo('inspection', 'Kontrak berakhir — menunggu inspeksi.');
                    } catch (\Throwable) {
                        // room mungkin sudah dipindahkan; abaikan
                    }
                }
            }
        });
    }

    /**
     * Sinkronkan status kamar dengan status lease.
     * Dipanggil otomatis saat lease disimpan.
     */
    public function syncRoomStatus(): void
    {
        $room = $this->room;

        if (!$room) {
            return;
        }

        if ($this->isOperational() && !in_array($room->status, ['occupied', 'notice_given'], true)) {
            $room->forceFill(['status' => 'occupied'])->saveQuietly();
        } elseif (!$this->isOperational()
            && in_array($room->status, ['occupied'], true)
            && !$room->leases()->whereIn('status', ['active', 'expiring_soon'])->whereKeyNot($this->getKey())->exists()) {
            // kamar tidak lagi punya tenant lain → biarkan workflow checkout menentukan
            // (jangan langsung available agar inspeksi tetap jalan)
        }
    }
}
