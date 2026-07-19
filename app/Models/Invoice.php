<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'lease_id', 'invoice_number', 'period_start', 'period_end',
        'due_date', 'base_amount', 'additional_charges', 'discount',
        'total', 'penalty', 'status', 'paid_at', 'payment_method',
        'payment_ref', 'payment_channel', 'payment_gateway_data',
        'notes', 'sent_at', 'reminder_sent_at',
    ];

    protected $casts = [
        'period_start'         => 'date',
        'period_end'           => 'date',
        'due_date'             => 'date',
        'paid_at'              => 'datetime',
        'sent_at'              => 'datetime',
        'reminder_sent_at'     => 'datetime',
        'additional_charges'   => 'array',
        'payment_gateway_data' => 'array',
        'base_amount'          => 'float',
        'discount'             => 'float',
        'total'                => 'float',
        'penalty'              => 'float',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function getTotalWithPenaltyAttribute(): float
    {
        return $this->total + $this->penalty;
    }

    // Auto-hitung penalty saat status berubah ke overdue
    public function calculatePenalty(): float
    {
        if (!$this->is_overdue) return 0;
        $daysLate   = now()->diffInDays($this->due_date);
        $penaltyPct = (float) setting('invoice_penalty_percent', 2);
        return round($this->total * ($penaltyPct / 100) * ceil($daysLate / 30), 0);
    }
}
