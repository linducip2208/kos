<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft'     => 'Draft',
        'sent'      => 'Ter Kirim',
        'partial'   => 'Sebagian Dibayar',
        'paid'      => 'Lunas',
        'overdue'   => 'Jatuh Tempo',
        'cancelled' => 'Dibatalkan',
    ];

    protected $fillable = [
        'lease_id', 'invoice_number', 'period_start', 'period_end',
        'due_date', 'base_amount', 'additional_charges', 'discount',
        'total', 'penalty', 'status', 'paid_at', 'payment_method',
        'payment_ref', 'payment_channel', 'payment_gateway_data',
        'notes', 'sent_at', 'reminder_sent_at', 'created_by',
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

    // ── Relations ────────────────────────────────────────────────────────

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /** Pembayaran terverifikasi (uang masuk nyata). */
    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)
            ->where('type', 'payment')
            ->where('status', 'verified');
    }

    /** Refund terverifikasi. */
    public function verifiedRefunds(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)
            ->where('type', 'refund')
            ->where('status', 'verified');
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    // ── Amount helpers ───────────────────────────────────────────────────

    public function getTotalWithPenaltyAttribute(): float
    {
        return round($this->total + $this->penalty, 2);
    }

    /**
     * Total yang harus dibayar (total + penalty − refund).
     */
    public function getPayableAmountAttribute(): float
    {
        $refunded = (float) $this->verifiedRefunds()->sum('amount');

        return max(0, round($this->getTotalWithPenaltyAttribute() - $refunded, 2));
    }

    /**
     * Sudah dibayar: dari payment records; fallback ke total untuk invoice lama
     * yang langsung ditandai paid tanpa record pembayaran (backward compat).
     */
    public function getPaidAmountAttribute(): float
    {
        $fromRecords = (float) $this->verifiedPayments()->sum('amount');

        if ($fromRecords === 0.0 && $this->status === 'paid') {
            return $this->getPayableAmountAttribute();
        }

        return min($fromRecords, $this->getPayableAmountAttribute());
    }

    public function getBalanceDueAttribute(): float
    {
        return round($this->payable_amount - $this->paid_amount, 2);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->balance_due <= 0.009;
    }

    public function getIsOverdueAttribute(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled', 'draft'], true)
            && $this->due_date && $this->due_date->isPast();
    }

    // ── Status sync ──────────────────────────────────────────────────────

    /**
     * Sinkronkan status berdasarkan pembayaran masuk.
     * Dipanggil oleh PaymentService setiap mutasi pembayaran.
     */
    public function syncPaymentStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $balance = $this->balance_due;
        $overdue = $this->is_overdue;

        if ($this->status !== 'paid' && $this->verifiedPayments()->exists() && !$this->sent_at) {
            $this->sent_at = now();
        }

        if ($balance <= 0.009) {
            if ($this->status !== 'paid') {
                $this->status  = 'paid';
                $this->paid_at = $this->paid_at ?? now();
                if (!$this->payment_method && $last = $this->payments()->verified()->latest('paid_at')->first()) {
                    $this->payment_method = $last->method;
                    $this->payment_ref    = $last->reference;
                    $this->payment_channel = $last->method;
                }
            }
        } elseif ($this->status === 'paid') {
            // refund membuat invoice tidak lunas lagi
            $this->status  = $overdue ? 'overdue' : ($this->sent_at ? 'partial' : 'sent');
            $this->paid_at = null;
        } elseif ($this->status !== 'overdue') {
            $paid = $this->paid_amount;

            if ($paid > 0) {
                $this->status = 'partial';
            } elseif ($overdue) {
                $this->status = 'overdue';
            }
        }

        $this->save();
    }

    // ── Penalty ──────────────────────────────────────────────────────────

    public function calculatePenalty(): float
    {
        if (!$this->is_overdue) {
            return 0;
        }

        $daysLate   = now()->diffInDays($this->due_date);
        $penaltyPct = (float) setting('invoice_penalty_percent', 2);

        return round($this->total * ($penaltyPct / 100) * ceil($daysLate / 30), 0);
    }
}
