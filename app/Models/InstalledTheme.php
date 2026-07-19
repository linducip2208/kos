<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstalledTheme extends Model
{
    protected $fillable = [
        'area', 'slug', 'name', 'version',
        'is_active', 'settings', 'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];
}
