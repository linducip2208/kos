<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\User;
use App\Notifications\InvoiceDueNotification;
use App\Notifications\MaintenanceSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_notification_lifecycle(): void
    {
        $admin = User::factory()->create(['role' => 'owner', 'is_active' => true]);
        $lease = Lease::factory()->create();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id]);
        $admin->notify(new InvoiceDueNotification($invoice));
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/notifications')->assertOk();
        $this->getJson('/api/v1/admin/notifications/unread-count')->assertJsonPath('count', 1);

        $id = $admin->notifications()->first()->id;
        $this->patchJson("/api/v1/admin/notifications/{$id}/read")->assertOk();
        $this->getJson('/api/v1/admin/notifications/unread-count')->assertJsonPath('count', 0);

        $this->postJson('/api/v1/admin/notifications/read-all')->assertOk();
        $this->deleteJson("/api/v1/admin/notifications/{$id}")->assertOk();
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_tenant_only_sees_own_notifications(): void
    {
        $a = Occupant::factory()->withPortalAccess()->create();
        $b = Occupant::factory()->withPortalAccess()->create();
        $a->notify(new MaintenanceSubmittedNotification(
            MaintenanceRequest::factory()->create()
        ));

        Sanctum::actingAs($b);
        $this->getJson('/api/v1/tenant/notifications')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/tenant/notifications/unread-count')->assertJsonPath('count', 0);

        Sanctum::actingAs($a);
        $this->getJson('/api/v1/tenant/notifications')->assertOk()->assertJsonCount(1, 'data');

        // Tenant B tidak bisa hapus notifikasi tenant A.
        Sanctum::actingAs($b);
        $id = $a->notifications()->first()->id;
        $this->deleteJson("/api/v1/tenant/notifications/{$id}")->assertNotFound();
    }
}
