<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EContract extends Model
{
    protected $table = 'e_contracts';

    protected $fillable = [
        'lease_id', 'contract_number', 'template_used',
        'content_html', 'owner_signature', 'owner_signed_at',
        'occupant_signature', 'occupant_signed_at',
        'pdf_path', 'status', 'sign_token', 'sign_token_expires_at',
    ];

    protected $casts = [
        'owner_signed_at'     => 'datetime',
        'occupant_signed_at'  => 'datetime',
        'sign_token_expires_at' => 'datetime',
    ];

    public function lease(): BelongsTo { return $this->belongsTo(Lease::class); }

    public function isFullySigned(): bool
    {
        return !is_null($this->owner_signature) && !is_null($this->occupant_signature);
    }

    public function generateSignToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'sign_token'             => $token,
            'sign_token_expires_at'  => now()->addDays(7),
        ]);
        return $token;
    }

    public function isTokenValid(string $token): bool
    {
        return $this->sign_token === $token
            && $this->sign_token_expires_at?->isFuture();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'        => 'Draft',        'sent'         => 'Terkirim',
            'owner_signed' => 'TTD Pemilik',  'fully_signed' => 'Ditandatangani',
            'expired'      => 'Kedaluwarsa',  default        => $this->status,
        };
    }
}
