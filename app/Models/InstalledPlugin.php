<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalledPlugin extends Model
{
    protected $fillable = [
        'plugin_slug', 'version', 'activation_key',
        'checksum', 'is_active', 'settings',
        'installed_at', 'activated_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'settings'     => 'array',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];
}
