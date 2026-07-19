<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSubmission extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id', 'name', 'phone', 'email',
        'subject', 'message', 'status', 'reply', 'replied_at', 'ip_address', 'tenant_id',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new'     => 'warning',
            'read'    => 'info',
            'replied' => 'success',
            default   => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new'     => 'Baru',
            'read'    => 'Dibaca',
            'replied' => 'Dibalas',
            default   => $this->status,
        };
    }
}
