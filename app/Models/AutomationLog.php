<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AutomationLog extends Model
{
    public const STATUS = ['triggered', 'success', 'failed', 'skipped'];

    public $timestamps = false;

    protected $fillable = [
        'rule_key', 'channel', 'subject_type', 'subject_id',
        'recipient', 'status', 'attempts', 'message', 'meta', 'created_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
        'attempts'   => 'integer',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeRule($query, string $ruleKey)
    {
        return $query->where('rule_key', $ruleKey);
    }

    public function markSuccess(): void
    {
        $this->update(['status' => 'success']);
    }

    public function markFailed(string $error): void
    {
        $this->increment('attempts');
        $this->update(['status' => 'failed', 'message' => $error]);
    }
}
