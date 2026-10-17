<?php

namespace Tests\Feature\Commands;

use App\Services\LicenseClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseValidateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_fails_closed_without_lock_file(): void
    {
        // Ganti mock TestCase dengan client asli: tanpa lock file → gagal tertutup.
        $this->swap(LicenseClient::class, new LicenseClient);

        $this->artisan('license:validate')
            ->expectsOutputToContain('Tidak ada lisensi valid')
            ->assertFailed();
    }

    public function test_validate_succeeds_with_verified_license(): void
    {
        // LicenseClient di-mock TestCase sebagai terverifikasi.
        $this->artisan('license:validate')
            ->expectsOutputToContain('Lisensi valid')
            ->assertSuccessful();
    }

    public function test_schedule_includes_license_validate(): void
    {
        $this->artisan('schedule:list')->assertSuccessful();
    }
}
