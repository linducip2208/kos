<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Occupant extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp', 'id_number', 'id_type',
        'id_photo', 'selfie_photo', 'address', 'occupation', 'workplace',
        'emergency_contact', 'notes',
        'portal_password', 'portal_active', 'portal_last_login',
    ];

    protected $hidden = ['portal_password', 'remember_token'];

    protected $casts = [
        'emergency_contact' => 'array',
        'portal_active'     => 'boolean',
        'portal_last_login' => 'datetime',
    ];

    public function getAuthPasswordName(): string { return 'portal_password'; }
    public function getAuthPassword(): string     { return $this->portal_password; }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getWhatsappNumberAttribute(): string
    {
        return $this->whatsapp ?? $this->phone;
    }

    public function setPortalPassword(string $password): void
    {
        $this->update(['portal_password' => Hash::make($password)]);
    }

    public function hasPortalAccess(): bool
    {
        return !is_null($this->portal_password) && $this->portal_active;
    }
}
