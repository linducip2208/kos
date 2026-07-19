<?php

namespace Tests;

use App\Core\License\LicenseService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock license so web routes are not blocked during tests.
        $this->mock(LicenseService::class, function ($mock) {
            $mock->shouldReceive('validate')->andReturn([
                'valid'   => true,
                'success' => true,
                'message' => 'License valid (test mode).',
            ]);
            $mock->shouldReceive('info')->andReturn([
                'key'          => 'TEST-KEY',
                'status'       => 'active',
                'product'      => 'koskosan',
                'version'      => '1.0.0',
                'type'         => 'regular',
                'activated_at' => now()->toISOString(),
                'expires_at'   => now()->addYear()->toDateString(),
            ]);
        });
    }
}
