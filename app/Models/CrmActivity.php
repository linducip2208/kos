<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivity extends Model
{
    public const TYPES = [
        'note'         => 'Catatan',
        'call'         => 'Telepon',
        'whatsapp'     => 'WhatsApp',
        'email'        => 'Email',
        'viewing'      => 'Survey/Scheduling',
        'stage_change' => 'Perubahan Stage',
    ];

    public $timestamps = false;

    protected $fillable = [
        'booking_request_id', 'user_id', 'type', 'subject',
        'description', 'next_follow_up_at', 'created_at',
    ];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
        'created_at'        => 'datetime',
    ];

    public function bookingRequest(): BelongsTo { return $this->belongsTo(BookingRequest::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
