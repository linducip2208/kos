<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        return Setting::get($key, $default, $group);
    }
}

if (!function_exists('setting_set')) {
    function setting_set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        Setting::set($key, $value, $group, $type);
    }
}

if (!function_exists('photo_url')) {
    /**
     * URL publik untuk path foto. Menerima path relatif storage
     * ('seed/mass/ext-01.jpg') maupun URL penuh (data lama picsum)
     * agar tidak pernah menghasilkan '/storage/https://...'.
     */
    function photo_url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}

if (!function_exists('active_theme')) {
    function active_theme(string $area): string
    {
        return app('theme.manager')->getActive($area);
    }
}

if (!function_exists('theme_view')) {
    function theme_view(string $area, string $view): string
    {
        $theme = active_theme($area);
        $path  = "themes.{$area}.{$theme}.views.{$view}";

        return view()->exists($path) ? $path : "themes.{$area}.default.views.{$view}";
    }
}
