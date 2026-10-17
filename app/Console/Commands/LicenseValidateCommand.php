<?php

namespace App\Console\Commands;

use App\Services\LicenseClient;
use Illuminate\Console\Command;

class LicenseValidateCommand extends Command
{
    protected $signature = 'license:validate';

    protected $description = 'Validasi lock lisensi lokal dan refresh heartbeat bila perlu';

    public function handle(LicenseClient $client): int
    {
        $domain = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));

        try {
            $data = $client->verify($domain);
        } catch (\Throwable $e) {
            $this->error('Validasi lisensi gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $data) {
            $this->warn("Tidak ada lisensi valid untuk domain {$domain}. Pairing ulang via /__pair.");

            return self::FAILURE;
        }

        $this->info("Lisensi valid: {$data['product']} ({$data['status']}), kedaluwarsa {$data['expires_at']}.");

        return self::SUCCESS;
    }
}
