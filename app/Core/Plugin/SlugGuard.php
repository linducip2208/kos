<?php

namespace App\Core\Plugin;

/**
 * Sanitasi slug/area agar path filesystem tidak bisa di-traverse
 * (mis. `../`, absolute path, backslash, null-byte).
 */
final class SlugGuard
{
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function plugin(?string $slug): ?string
    {
        $slug = is_string($slug) ? trim($slug) : '';

        if ($slug === '' || strlen($slug) > 100) {
            return null;
        }

        return preg_match(self::PATTERN, $slug) ? $slug : null;
    }

    public static function themeArea(?string $area): ?string
    {
        $allowed = config('themes.areas', ['admin', 'user', 'frontend']);

        return is_string($area) && in_array($area, $allowed, true) ? $area : null;
    }

    /**
     * Pastikan $path berada di dalam $base (cegah traversal + symlink escape).
     */
    public static function containedIn(string $base, string $path): bool
    {
        $realBase = realpath($base);
        $realPath = realpath($path) ?: realpath(dirname($path));

        return $realBase !== false && $realPath !== false
            && str_starts_with($realPath, $realBase);
    }
}
