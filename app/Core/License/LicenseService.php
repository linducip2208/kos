<?php

namespace App\Core\License;

use App\Services\LicenseClient;
use Illuminate\Support\Facades\Log;

/**
 * Canonical facade untuk lisensi.
 *
 * v3 (LicenseClient + .license.lock AES-GCM + RSA) adalah sumber kebenaran
 * tunggal. Class ini dipertahankan sebagai adapter agar controller/Filament
 * lama (info/activate/validate/revoke/checkUpdate) tidak perlu diubah.
 */
class LicenseService
{
    public function __construct(private LicenseClient $client) {}

    private function domain(): string
    {
        return strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?: request()->getHost() ?: gethostname());
    }

    public function activate(string $activationKey): array
    {
        $res = $this->client->activate($activationKey, $this->domain());

        if ($res['ok'] ?? false) {
            return ['success' => true, 'message' => 'Lisensi berhasil diaktifkan.', 'data' => $res['data'] ?? null];
        }

        return ['success' => false, 'message' => $res['error'] ?? 'Aktivasi gagal.'];
    }

    public function validate(): array
    {
        $data = $this->client->verify($this->domain());

        if ($data) {
            return ['success' => true, 'valid' => true, 'message' => 'Lisensi valid.', 'data' => $data];
        }

        return ['success' => false, 'valid' => false, 'message' => 'Lisensi tidak valid atau belum dipairing. Buka /__pair.'];
    }

    public function revoke(string $activationKey): array
    {
        try {
            $this->client->clearLock();
        } catch (\Throwable $e) {
            Log::warning('License revoke error: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Lisensi dicabut.'];
    }

    public function info(): array
    {
        $data = $this->client->verify($this->domain());

        if (!$data) {
            return ['key' => 'Belum diaktifkan', 'status' => 'inactive', 'product' => null,
                'version' => null, 'type' => null, 'activated_at' => null, 'expires_at' => null];
        }

        return [
            'key'          => $data['installation_id'] ?? 'Aktif',
            'status'       => $data['status'] ?? 'active',
            'product'      => $data['product'] ?? null,
            'version'      => null,
            'type'         => $data['plan'] ?? null,
            'activated_at' => $data['issued_at'] ?? null,
            'expires_at'   => $data['expires_at'] ?? null,
        ];
    }

    public function checkUpdate(): array
    {
        return ['success' => false, 'has_update' => false];
    }
}
