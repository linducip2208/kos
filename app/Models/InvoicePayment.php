<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    public const METHODS = [
        'cash'     => 'Tunai',
        'transfer' => 'Transfer Bank',
        'gateway'  => 'Payment Gateway',
        'qris'     => 'QRIS',
        'va'       => 'Virtual Account',
        'other'    => 'Lainnya',
    ];

    protected $fillable = [
        'invoice_id', 'type', 'amount', 'method', 'reference', 'proof_path',
        'paid_at', 'received_by', 'verified_by', 'verified_at', 'status',
        'rejection_reason', 'reason', 'payment_transaction_id', 'notes',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'paid_at'    => 'datetime',
        'verified_at'=> 'datetime',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function receivedBy(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function paymentTransaction(): BelongsTo { return $this->belongsTo(PaymentTransaction::class); }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    // Scope helper untuk query verified
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
