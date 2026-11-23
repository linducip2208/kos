<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Semua artisan command kustom harus terdaftar dan yang aman dijalankan
 * harus sukses. Uji eksekusi memakai database testing (RefreshDatabase),
 * jadi tidak menyentuh data dev/produksi.
 */
class AllCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_custom_commands_are_registered(): void
    {
        $commands = Artisan::all();

        foreach ([
            'app:smoke-test',
            'invoices:send-reminders',
            'invoices:generate',
            'invoices:mark-overdue',
            'lease:check-expiring',
            'license:validate',
            'portal:set-password',
            'koskosan:create-owner',
            'koskosan:remove-demo',
            'backup:run',
            'seo:indexnow',
            'plugin:list', 'plugin:install', 'plugin:activate',
            'plugin:deactivate', 'plugin:uninstall',
            'theme:list', 'theme:activate',
            'make:plugin', 'make:theme',
        ] as $signature) {
            $this->assertArrayHasKey($signature, $commands, "Command {$signature} tidak terdaftar");
        }
    }

    public function test_read_only_commands_succeed(): void
    {
        $this->artisan('plugin:list')->assertSuccessful();
        $this->artisan('theme:list')->assertSuccessful();
        $this->artisan('invoices:generate', ['--dry-run' => true])->assertSuccessful();
        $this->artisan('lease:check-expiring')->assertSuccessful();
        $this->artisan('license:validate')->assertSuccessful();
    }

    public function test_mark_overdue_runs_without_error(): void
    {
        $this->artisan('invoices:mark-overdue')->assertSuccessful();
    }
}
