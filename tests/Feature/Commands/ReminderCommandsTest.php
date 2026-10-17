<?php

namespace Tests\Feature\Commands;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lease_check_expiring_includes_expiring_soon_and_dedupes(): void
    {
        $admin = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $lease = Lease::factory()->create([
            'status' => 'active', 'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $this->assertSame('expiring_soon', $lease->fresh()->status);

        $this->artisan('lease:check-expiring')->assertSuccessful();
        $this->artisan('lease:check-expiring')->assertSuccessful();

        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_send_reminders_dedupes_admin_notifications_without_gateway(): void
    {
        Setting::set('whatsapp_enabled', '1', 'notif');
        Setting::set('reminder_days', '3');
        Setting::set('whatsapp_api_key', '', 'notif');
        $admin = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $lease = Lease::factory()->create(['status' => 'active']);
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'status' => 'sent',
            'due_date' => now()->addDays(3)->toDateString(),
            'reminder_sent_at' => null,
        ]);

        $this->artisan('invoices:send-reminders')->assertSuccessful();
        $this->artisan('invoices:send-reminders')->assertSuccessful();

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertNull($invoice->fresh()->reminder_sent_at);
    }
}
