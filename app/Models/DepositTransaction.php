<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DepositTransaction extends Model
{
    public const TYPES = [
        'receipt'   => 'Penerimaan',
        'deduction' => 'Potongan',
        'refund'    => 'Refund',
        'forfeit'   => 'Hangus',
        'adjustment'=> 'Penyesuaian',
    ];

    protected $fillable = [
        'deposit_id', 'type', 'amount', 'reason', 'method', 'reference',
        'source_type', 'source_id', 'recorded_by', 'occurred_at', 'balance_after',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
        'occurred_at'   => 'date',
    ];

    public function deposit(): BelongsTo { return $this->belongsTo(Deposit::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
    public function source(): MorphTo { return $this->morphTo(); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /** Tanda mutasi: receipt/adjustment positif masuk; lainnya keluar. */
    public function getSignedAmountAttribute(): float
    {
        return in_array($this->type, ['receipt', 'adjustment'], true)
            ? (float) $this->amount
            : -(float) $this->amount;
    }
}
