<?php

namespace Tests\Feature;

use App\Services\LicenseClient;
use Tests\TestCase;

/**
 * RequirePair wajib memblokir SEMUA route (web + api) saat aplikasi belum dipairing:
 * API menjawab JSON 423, web redirect ke wizard /__pair.
 */
class LicensePairMiddlewareTest extends TestCase
{
    private function unpair(): void
    {
        // Timpa mock bawaan Tests\TestCase (verify → valid) agar simulasi belum pairing.
        $this->mock(LicenseClient::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturnNull();
            $mock->shouldReceive('isPaired')->andReturnFalse();
        });
    }

    public function test_unpaired_api_returns_json_423(): void
    {
        $this->unpair();

        $this->getJson('/api/v1/public/properties')
            ->assertStatus(423)
            ->assertJsonPath('message', 'Aplikasi belum dipairing lisensi.');
    }

    public function test_unpaired_web_redirects_to_pair_wizard(): void
    {
        $this->unpair();

        $this->get('/')->assertRedirect('/__pair');
    }

    public function test_pair_wizard_itself_stays_accessible(): void
    {
        $this->unpair();

        $this->get('/__pair')->assertOk();
    }
}
