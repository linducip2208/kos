<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\PaymentTransaction;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regression untuk hardening keamanan API admin + webhook + akun nonaktif.
 */
class AdminSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function scopedManager(Property $property): User
    {
        $manager = User::factory()->create(['role' => 'property_manager', 'is_active' => true]);
        $manager->properties()->attach($property->id);

        return $manager;
    }

    public function test_scoped_manager_dashboard_summary_only_sees_assigned_property(): void
    {
        $propA = Property::factory()->create();
        $propB = Property::factory()->create();
        Room::factory()->occupied()->create(['property_id' => $propA->id]);
        Room::factory()->occupied()->create(['property_id' => $propA->id]);
        Room::factory()->occupied()->create(['property_id' => $propB->id]);

        Sanctum::actingAs($this->scopedManager($propA));

        $this->getJson('/api/v1/admin/dashboard/summary')->assertOk()
            ->assertJsonPath('rooms.total', 2)
            ->assertJsonPath('rooms.occupied', 2);
    }

    public function test_scoped_manager_upcoming_and_recent_only_sees_assigned_property(): void
    {
        $propA = Property::factory()->create();
        $propB = Property::factory()->create();
        $roomA = Room::factory()->occupied()->create(['property_id' => $propA->id]);
        $roomB = Room::factory()->occupied()->create(['property_id' => $propB->id]);
        $leaseA = Lease::factory()->create([
            'room_id' => $roomA->id, 'status' => 'active', 'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $leaseB = Lease::factory()->create([
            'room_id' => $roomB->id, 'status' => 'active', 'end_date' => now()->addDays(10)->toDateString(),
        ]);
        Invoice::factory()->create(['lease_id' => $leaseA->id, 'status' => 'overdue']);
        Invoice::factory()->create(['lease_id' => $leaseB->id, 'status' => 'overdue']);

        Sanctum::actingAs($this->scopedManager($propA));

        $upcoming = $this->getJson('/api/v1/admin/dashboard/upcoming')->assertOk()->json();
        $this->assertCount(1, $upcoming['expiring_leases']);
        $this->assertSame($leaseA->id, $upcoming['expiring_leases'][0]['id']);
        $this->assertCount(1, $upcoming['due_soon_invoices']);

        $recent = $this->getJson('/api/v1/admin/dashboard/recent-activity')->assertOk()->json();
        $this->assertCount(1, $recent['recent_invoices']);
        $this->assertCount(1, $recent['recent_leases']);
    }

    public function test_user_without_permission_cannot_list_users_but_owner_can(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'property_manager', 'is_active' => true]));
        $this->getJson('/api/v1/admin/users')->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));
        $this->getJson('/api/v1/admin/users')->assertOk();
    }

    public function test_inactive_admin_token_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'owner', 'is_active' => false]));
        $this->getJson('/api/v1/admin/dashboard/summary')->assertForbidden();
        $this->getJson('/api/v1/admin/properties')->assertForbidden();
    }

    public function test_inactive_occupant_token_is_rejected(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['portal_active' => false]);
        Sanctum::actingAs($occupant);
        $this->getJson('/api/v1/tenant/dashboard')->assertForbidden();
    }

    public function test_scoped_manager_export_only_contains_assigned_property(): void
    {
        $propA = Property::factory()->create();
        $propB = Property::factory()->create();
        $roomA = Room::factory()->create(['property_id' => $propA->id]);
        $roomB = Room::factory()->create(['property_id' => $propB->id]);
        $leaseA = Lease::factory()->create(['room_id' => $roomA->id]);
        $leaseB = Lease::factory()->create(['room_id' => $roomB->id]);
        $invA = Invoice::factory()->create(['lease_id' => $leaseA->id]);
        Invoice::factory()->create(['lease_id' => $leaseB->id]);

        Sanctum::actingAs($this->scopedManager($propA));

        $response = $this->get('/api/v1/admin/export?type=invoices&format=csv');
        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString($invA->invoice_number, $content);
        $this->assertStringNotContainsString($propB->name, $content);
    }

    public function test_other_property_invoice_pdf_returns_forbidden_not_error(): void
    {
        $propA = Property::factory()->create();
        $propB = Property::factory()->create();
        $roomB = Room::factory()->create(['property_id' => $propB->id]);
        $leaseB = Lease::factory()->create(['room_id' => $roomB->id]);
        $invB = Invoice::factory()->create(['lease_id' => $leaseB->id]);

        Sanctum::actingAs($this->scopedManager($propA));

        $this->get("/api/v1/admin/export/invoices/{$invB->id}/pdf")->assertForbidden();
    }

    public function test_leases_expiring_includes_auto_flagged_expiring_soon(): void
    {
        $lease = Lease::factory()->create([
            'status' => 'active', 'end_date' => now()->addDays(10)->toDateString(),
        ]);
        $this->assertSame('expiring_soon', $lease->fresh()->status);

        Sanctum::actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));

        $this->getJson('/api/v1/admin/leases/expiring')->assertOk()
            ->assertJsonFragment(['id' => $lease->id]);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        \App\Models\Setting::set('midtrans_server_key', 'dummy-key-for-test', 'payment');
        \App\Models\Setting::set('tripay_private_key', 'dummy-key-for-test', 'payment');

        $this->postJson('/webhook/midtrans', ['order_id' => 'X'])->assertStatus(403);
        $this->postJson('/webhook/tripay', [])->assertStatus(403);
    }

    public function test_webhook_without_configured_keys_fails_closed(): void
    {
        $this->postJson('/webhook/midtrans', ['order_id' => 'X'])->assertStatus(500);
        $this->postJson('/webhook/tripay', [])->assertStatus(500);
    }

    public function test_webhook_replayed_success_is_idempotent(): void
    {
        $lease = Lease::factory()->create();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000]);
        \App\Models\Setting::set('payment_gateway_active', 'manual');
        $trx = PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'gateway' => 'midtrans',
            'order_id' => 'KOS-REPLAY-1',
            'amount' => 1000000,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        $payload = [
            'order_id' => 'KOS-REPLAY-1',
            'status_code' => '200',
            'gross_amount' => '1000000.00',
            'transaction_status' => 'settlement',
        ];
        \App\Models\Setting::set('midtrans_server_key', 'dummy-key-for-test', 'payment');
        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'dummy-key-for-test');

        $this->postJson('/webhook/midtrans', $payload)->assertOk();
        $this->assertSame('success', $trx->fresh()->status);
    }

    public function test_webhook_with_wrong_amount_does_not_pay_invoice(): void
    {
        $lease = Lease::factory()->create();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000, 'status' => 'sent']);
        PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'gateway' => 'midtrans',
            'order_id' => 'KOS-AMOUNT-1',
            'amount' => 1000000,
            'status' => 'pending',
        ]);

        \App\Models\Setting::set('midtrans_server_key', 'dummy-key-for-test', 'payment');
        $payload = [
            'order_id' => 'KOS-AMOUNT-1',
            'status_code' => '200',
            'gross_amount' => '100.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'dummy-key-for-test');

        $this->postJson('/webhook/midtrans', $payload)->assertOk();
        $this->assertSame('failed', PaymentTransaction::where('order_id', 'KOS-AMOUNT-1')->first()->status);
        $this->assertNotSame('paid', $invoice->fresh()->status);
    }

    public function test_webhook_out_of_order_deny_after_success_keeps_paid(): void
    {
        $lease = Lease::factory()->create();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000, 'status' => 'paid']);
        PaymentTransaction::create([
            'invoice_id' => $invoice->id,
            'gateway' => 'midtrans',
            'order_id' => 'KOS-OOO-1',
            'amount' => 1000000,
            'status' => 'success',
            'paid_at' => now(),
        ]);

        \App\Models\Setting::set('midtrans_server_key', 'dummy-key-for-test', 'payment');
        $payload = [
            'order_id' => 'KOS-OOO-1',
            'status_code' => '200',
            'gross_amount' => '1000000.00',
            'transaction_status' => 'deny',
        ];
        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'dummy-key-for-test');

        $this->postJson('/webhook/midtrans', $payload)->assertOk();
        $this->assertSame('success', PaymentTransaction::where('order_id', 'KOS-OOO-1')->first()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_webhook_unknown_order_is_ignored(): void
    {
        \App\Models\Setting::set('midtrans_server_key', 'dummy-key-for-test', 'payment');
        $payload = [
            'order_id' => 'KOS-NOT-EXIST',
            'status_code' => '200',
            'gross_amount' => '1000000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'dummy-key-for-test');

        $this->postJson('/webhook/midtrans', $payload)->assertOk();
        $this->assertSame(0, PaymentTransaction::where('order_id', 'KOS-NOT-EXIST')->count());
    }
}
