<?php

namespace App\Filament\Concerns;

use Illuminate\Support\Facades\Gate;

trait AuthorizesPageAccess
{
    public static function canAccess(): bool
    {
        return property_exists(static::class, 'permission')
            && static::$permission !== null
            && Gate::allows(static::$permission);
    }
}
