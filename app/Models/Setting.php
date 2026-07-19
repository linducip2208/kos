<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function get(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        return Cache::rememberForever("setting:{$group}:{$key}", function () use ($key, $group, $default) {
            try {
                $setting = static::where('group', $group)->where('key', $key)->first();
                if (!$setting) return $default;
                return static::cast($setting->value, $setting->type);
            } catch (\Throwable) {
                return $default;
            }
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'string'): void
    {
        $stored = match ($type) {
            'json'      => json_encode($value),
            'encrypted' => Crypt::encryptString((string) $value),
            'boolean'   => $value ? '1' : '0',
            default     => (string) $value,
        };

        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $type]
        );

        Cache::forget("setting:{$group}:{$key}");
    }

    private static function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'boolean'   => (bool) $value,
            'integer'   => (int) $value,
            'json'      => json_decode($value, true),
            'encrypted' => Crypt::decryptString($value),
            default     => $value,
        };
    }
}
