<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    public const STATUSES = [
        'pending'        => 'Menunggu Bayar',
        'received'       => 'Diterima',
        'held'           => 'Ditahan',
        'partially_used' => 'Terpakai Sebagian',
        'refunded'       => 'Direfunding',
        'forfeited'      => 'Hangus',
    ];

    protected $fillable = [
        'tenant_id', 'lease_id', 'property_id', 'amount', 'type', 'status',
        'paid_at', 'payment_method', 'source_reference',
        'refunded_at', 'refunded_amount', 'notes',
    ];

    protected $casts = [
        'paid_at'         => 'date',
        'refunded_at'     => 'date',
        'amount'          => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    public function tenant() { return $this->belongsTo(Occupant::class, 'tenant_id'); }
    public function lease() { return $this->belongsTo(Lease::class); }
    public function property() { return $this->belongsTo(Property::class); }

    public function transactions()
    {
        return $this->hasMany(DepositTransaction::class)->orderBy('occurred_at')->orderBy('id');
    }

    /**
     * Saldo deposit berjalan = penerimaan − potongan − refund − hangus.
     * Backward compat: bila belum ada ledger, pakai kolom lama.
     */
    public function getBalanceAttribute(): float
    {
        if ($this->transactions()->exists()) {
            return round($this->transactions->sum('signed_amount'), 2);
        }

        // Legacy: amount − refunded_amount (kolom lama)
        return round((float) $this->amount - (float) $this->refunded_amount, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'        => 'gray',
            'received', 'held' => 'success',
            'partially_used' => 'warning',
            'refunded'       => 'info',
            'forfeited'      => 'danger',
            default          => 'gray',
        };
    }

    public function getIsSettledAttribute(): bool
    {
        return in_array($this->status, ['refunded', 'forfeited'], true);
    }
}
