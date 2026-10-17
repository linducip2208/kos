<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    private function enable(array $overrides = []): void
    {
        Setting::set('whatsapp_enabled', '1', 'notif');
        Setting::set('whatsapp_api_key', 'test-key', 'notif');
        Setting::set('whatsapp_sender', '6281000000000', 'notif');
        Setting::set('whatsapp_api_url', 'https://wa.test', 'notif');
        foreach ($overrides as $key => $value) {
            Setting::set($key, $value, 'notif');
        }
    }

    public function test_disabled_gateway_queues_log_without_sending(): void
    {
        Http::fake();
        Http::preventStrayRequests();

        $this->assertFalse(app(WhatsAppService::class)->send('081234567890', 'Halo'));

        Http::assertNothingSent();
        $log = WhatsappLog::first();
        $this->assertSame('queued', $log->status);
        $this->assertSame('6281234567890', $log->to_number);
    }

    public function test_successful_send_marks_log_sent(): void
    {
        $this->enable();
        Http::fake(['wa.test/*' => Http::response(['status' => true], 200)]);

        $this->assertTrue(app(WhatsAppService::class)->send('081234567890', 'Tagihan'));

        $log = WhatsappLog::first();
        $this->assertSame('sent', $log->status);
        $this->assertNotNull($log->sent_at);
    }

    public function test_provider_failure_marks_log_failed_safely(): void
    {
        $this->enable();
        Http::fake(['wa.test/*' => Http::response(['status' => false, 'reason' => 'invalid number'], 400)]);

        $this->assertFalse(app(WhatsAppService::class)->send('081234567890', 'Tagihan'));

        $log = WhatsappLog::first();
        $this->assertSame('failed', $log->status);
        $this->assertNotNull($log->error_message);
        $this->assertNull($log->sent_at);
    }

    public function test_provider_exception_marks_log_failed(): void
    {
        $this->enable();
        Http::fake(['wa.test/*' => function () {
            throw new \RuntimeException('connection timeout');
        }]);

        $this->assertFalse(app(WhatsAppService::class)->send('081234567890', 'Tagihan'));
        $this->assertSame('failed', WhatsappLog::first()->status);
    }

    public function test_blast_counts_only_successful_sends(): void
    {
        $this->enable();
        Http::fake(['wa.test/*' => Http::response(['status' => true], 200)]);

        $sent = app(WhatsAppService::class)->blast(['0811', '0822'], 'Pengumuman');

        $this->assertSame(2, $sent);
        $this->assertSame(2, WhatsappLog::where('type', 'blast')->where('status', 'sent')->count());
    }
}
