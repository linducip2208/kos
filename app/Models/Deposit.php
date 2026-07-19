<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = ['tenant_id', 'lease_id', 'amount', 'type', 'status', 'paid_at', 'refunded_at', 'refunded_amount', 'notes'];

    protected $casts = ['paid_at' => 'date', 'refunded_at' => 'date', 'amount' => 'decimal:2', 'refunded_amount' => 'decimal:2'];

    public function tenant() { return $this->belongsTo(Occupant::class, 'tenant_id'); }
    public function lease() { return $this->belongsTo(Lease::class); }
}
