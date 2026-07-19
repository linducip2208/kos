<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsappLog extends Model
{
    protected $fillable = [
        'to_number', 'to_name', 'message', 'type',
        'notifiable_type', 'notifiable_id',
        'status', 'error_message', 'gateway_response', 'sent_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'sent_at'          => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
