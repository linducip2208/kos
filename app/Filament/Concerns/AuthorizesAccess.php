<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Pusatkan otorisasi Filament: Resource/Page/Widget cukup deklarasikan
 * `$permission` (gate untuk lihat) dan opsional `$managePermission`
 * (gate untuk create/edit/delete). Default manage = '<prefix>.manage'.
 *
 * Owner & super_admin dibypass lewat Gate::before().
 */
trait AuthorizesAccess
{
    /** Gate untuk melihat resource/page/widget, mis. 'room.view'. */
    protected static ?string $permission = null;

    /** Gate untuk create/edit/delete. Null = diturunkan dari $permission. */
    protected static ?string $managePermission = null;

    public static function canViewAny(): bool
    {
        return static::$permission === null || Gate::allows(static::$permission);
    }

    public static function canCreate(): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canEdit(Model $record): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canAccess(): bool
    {
        return static::$permission === null || Gate::allows(static::$permission);
    }

    public static function canView(): bool
    {
        return static::$permission === null || Gate::allows(static::$permission);
    }

    protected static function managePermission(): string
    {
        if (static::$managePermission !== null) {
            return static::$managePermission;
        }

        $perm = static::$permission ?? '';

        // 'foo.view' → 'foo.manage'; fallback kalau tidak berakhiran .view.
        return str_ends_with($perm, '.view')
            ? substr($perm, 0, -5) . '.manage'
            : ($perm !== '' ? $perm : 'settings.manage');
    }
}
