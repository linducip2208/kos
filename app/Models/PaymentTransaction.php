<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'invoice_id', 'gateway', 'transaction_id', 'order_id',
        'amount', 'payment_type', 'channel', 'status',
        'gateway_response', 'payment_url', 'expired_at', 'paid_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'expired_at'       => 'datetime',
        'paid_at'          => 'datetime',
        'amount'           => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
