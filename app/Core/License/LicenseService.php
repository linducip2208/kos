<?php

namespace App\Core\License;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    private string $apiBase;
    private ?string $secret;
    private string $domain;

    public function __construct()
    {
        $this->apiBase = config('license.api_base', 'https://whitelabel.co.id');
        $this->secret  = config('license.secret');
        $this->domain  = parse_url(config('app.url'), PHP_URL_HOST) ?: gethostname();
    }

    public function activate(string $activationKey): array
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiBase}/api/license/activate", [
                'activation_key' => $activationKey,
                'domain'         => $this->domain,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['activated'] ?? false) {
                    $checksum = $data['checksum'];
                    $this->persistChecksum($checksum);

                    Setting::set('license_key',          $activationKey,       'license');
                    Setting::set('license_checksum',     $checksum,            'license');
                    Setting::set('license_status',       'active',             'license');
                    Setting::set('license_activated_at', now()->toISOString(), 'license');

                    Cache::forget('license_valid');

                    return ['success' => true, 'message' => $data['message'] ?? 'Lisensi berhasil diaktifkan.'];
                }

                return ['success' => false, 'message' => $data['message'] ?? 'Aktivasi gagal.'];
            }

            $body = $response->body();
            return ['success' => false, 'message' => "Aktivasi gagal ({$response->status()}): {$body}"];
        } catch (\Exception $e) {
            Log::error('License activation error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function validate(): array
    {
        return Cache::remember('license_valid', config('license.cache_ttl', 21600), function () {
            $activationKey = config('license.key') ?: Setting::get('license_key', null, 'license');

            if (!$activationKey) {
                return ['success' => false, 'valid' => false, 'message' => 'Tidak ada lisensi yang diaktifkan.'];
            }

            try {
                $response = Http::timeout(5)->post("{$this->apiBase}/api/license/validate", [
                    'activation_key' => $activationKey,
                    'domain'         => $this->domain,
                ]);

                if ($response->successful() && ($response->json('valid') === true)) {
                    $meta = $response->json();

                    if ($checksum = $meta['checksum'] ?? null) {
                        $this->persistChecksum($checksum);
                        Setting::set('license_checksum', $checksum, 'license');
                    }

                    Setting::set('license_product',    $meta['product']    ?? null, 'license');
                    Setting::set('license_version',    $meta['version']    ?? null, 'license');
                    Setting::set('license_type',       $meta['type']       ?? null, 'license');
                    Setting::set('license_expires_at', $meta['expires_at'] ?? null, 'license');
                    Setting::set('license_status',     'active',                    'license');

                    return ['success' => true, 'valid' => true, 'message' => 'Lisensi valid.', 'data' => $meta];
                }

                if (in_array($response->status(), [403, 404])) {
                    return ['success' => false, 'valid' => false, 'message' => $response->body()];
                }
            } catch (\Exception) {
                return $this->validateOffline($activationKey);
            }

            return ['success' => false, 'valid' => false, 'message' => 'Lisensi tidak valid.'];
        });
    }

    public function revoke(string $activationKey): array
    {
        try {
            Http::timeout(10)->post("{$this->apiBase}/api/license/revoke", [
                'activation_key' => $activationKey,
                'domain'         => $this->domain,
            ]);
        } catch (\Exception) {
        }

        $sigPath = storage_path('license.sig');
        if (file_exists($sigPath)) {
            @unlink($sigPath);
        }

        Setting::set('license_key',      '',        'license');
        Setting::set('license_checksum', '',        'license');
        Setting::set('license_status',   'revoked', 'license');
        Cache::forget('license_valid');

        return ['success' => true, 'message' => 'Lisensi dicabut.'];
    }

    public function info(): array
    {
        return [
            'key'          => Setting::get('license_key',          'Belum diaktifkan', 'license'),
            'status'       => Setting::get('license_status',       'inactive',         'license'),
            'product'      => Setting::get('license_product',      null,               'license'),
            'version'      => Setting::get('license_version',      null,               'license'),
            'type'         => Setting::get('license_type',         null,               'license'),
            'activated_at' => Setting::get('license_activated_at', null,               'license'),
            'expires_at'   => Setting::get('license_expires_at',   null,               'license'),
        ];
    }

    public function checkUpdate(): array
    {
        $product = config('license.product', 'koskosan');
        $current = config('koskosan.version', '1.0.0');

        try {
            $response = Http::timeout(5)->get("{$this->apiBase}/api/version/check", [
                'product' => $product,
                'current' => $current,
            ]);

            if ($response->successful()) {
                return ['success' => true] + $response->json();
            }
        } catch (\Exception $e) {
            Log::warning('Version check error: ' . $e->getMessage());
        }

        return ['success' => false, 'has_update' => false];
    }

    private function validateOffline(string $activationKey): array
    {
        $storedChecksum = @file_get_contents(storage_path('license.sig'))
            ?: Setting::get('license_checksum', null, 'license');

        if (!$storedChecksum) {
            return ['success' => false, 'valid' => false, 'message' => 'Validasi offline gagal: tidak ada checksum tersimpan.'];
        }

        if (hash_equals($this->generateChecksum($activationKey), trim($storedChecksum))) {
            return ['success' => true, 'valid' => true, 'message' => 'Lisensi valid (mode offline).'];
        }

        return ['success' => false, 'valid' => false, 'message' => 'Lisensi tidak valid (mode offline).'];
    }

    private function generateChecksum(string $activationKey): string
    {
        $secret = $this->secret ?? config('app.key');
        return hash_hmac('sha256', $activationKey . '|' . $this->domain, $secret);
    }

    private function persistChecksum(string $checksum): void
    {
        @file_put_contents(storage_path('license.sig'), $checksum);
    }
}
