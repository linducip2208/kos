<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'is_active', 'tenant_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Role helpers ─────────────────────────────────────────────────────

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['owner', 'property_manager'], true);
    }

    public function isViewer(): bool
    {
        return $this->role === 'auditor';
    }

    public function getRoleLabelAttribute(): string
    {
        return Permissions::ROLES[$this->role] ?? $this->role;
    }

    // ── Property scoping ─────────────────────────────────────────────────

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    /**
     * ID property yang boleh dilihat user.
     * Owner/super_admin = semua (return null sebagai penanda "unscoped").
     */
    public function scopedPropertyIds(): ?array
    {
        if ($this->isOwner() || $this->isSuperAdmin()) {
            return null; // semua
        }

        return $this->properties()->pluck('properties.id')->all();
    }

    /** Scope query berdasarkan property yang ditugaskan. */
    public function scopeWithinProperties($query, string $column = 'property_id')
    {
        $ids = $this->scopedPropertyIds();

        return $ids === null ? $query : $query->whereIn($column, $ids ?: [0]);
    }
}
