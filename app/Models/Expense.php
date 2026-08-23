<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const STATUSES = [
        'pending'  => 'Menunggu Approval',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
    ];

    protected $fillable = [
        'property_id', 'category', 'description', 'amount', 'expense_date',
        'vendor', 'receipt_path', 'notes',
        'status', 'approved_by', 'approved_at', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'gray',
        };
    }

    /** Hanya expense disetujui yang dihitung dalam laporan keuangan. */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
