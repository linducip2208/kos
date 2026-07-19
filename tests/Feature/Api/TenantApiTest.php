<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use Tests\TestCase;

class TenantApiTest extends TestCase
{
    private Occupant $occupant;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->occupant = Occupant::factory()->withPortalAccess()->create();
        $this->token    = $this->occupant->createToken('test')->plainTextToken;
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function test_dashboard_returns_summary(): void
    {
        $this->withToken($this->token)
            ->getJson('/api/tenant/dashboard')
            ->assertOk()
            ->assertJsonStructure(['lease', 'unpaid_invoices', 'unpaid_total', 'open_maintenance']);
    }

    public function test_dashboard_includes_active_lease(): void
    {
        $room  = Room::factory()->create(['room_number' => 'B5']);
        $lease = Lease::factory()->create([
            'occupant_id' => $this->occupant->id,
            'room_id'     => $room->id,
            'status'      => 'active',
        ]);

        $this->withToken($this->token)
            ->getJson('/api/tenant/dashboard')
            ->assertOk()
            ->assertJsonPath('lease.room_number', 'B5');
    }

    // ── Lease ─────────────────────────────────────────────────────────────────

    public function test_lease_endpoint_returns_active_lease(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Bahagia']);
        $room     = Room::factory()->create(['property_id' => $property->id]);
        Lease::factory()->create([
            'occupant_id' => $this->occupant->id,
            'room_id'     => $room->id,
            'status'      => 'active',
        ]);

        $this->withToken($this->token)
            ->getJson('/api/tenant/lease')
            ->assertOk()
            ->assertJsonPath('room.property', 'Kos Bahagia');
    }

    public function test_lease_returns_404_when_no_active_lease(): void
    {
        $this->withToken($this->token)
            ->getJson('/api/tenant/lease')
            ->assertNotFound();
    }

    // ── Invoices ─────────────────────────────────────────────────────────────

    public function test_invoices_list_returns_only_own(): void
    {
        $lease   = Lease::factory()->create(['occupant_id' => $this->occupant->id]);
        Invoice::factory()->count(3)->create(['lease_id' => $lease->id]);

        $other = Occupant::factory()->create();
        $otherLease = Lease::factory()->create(['occupant_id' => $other->id]);
        Invoice::factory()->count(5)->create(['lease_id' => $otherLease->id]);

        $this->withToken($this->token)
            ->getJson('/api/tenant/invoices')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_invoice_show_returns_details(): void
    {
        $lease   = Lease::factory()->create(['occupant_id' => $this->occupant->id]);
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id]);

        $this->withToken($this->token)
            ->getJson("/api/tenant/invoices/{$invoice->id}")
            ->assertOk()
            ->assertJsonPath('invoice_number', $invoice->invoice_number);
    }

    public function test_invoice_show_forbidden_for_other_occupant(): void
    {
        $other      = Occupant::factory()->create();
        $otherLease = Lease::factory()->create(['occupant_id' => $other->id]);
        $invoice    = Invoice::factory()->create(['lease_id' => $otherLease->id]);

        $this->withToken($this->token)
            ->getJson("/api/tenant/invoices/{$invoice->id}")
            ->assertNotFound();
    }

    // ── Maintenance ──────────────────────────────────────────────────────────

    public function test_maintenance_list_returns_own_requests(): void
    {
        MaintenanceRequest::factory()->count(2)->create(['occupant_id' => $this->occupant->id]);
        MaintenanceRequest::factory()->count(3)->create(); // other occupants

        $this->withToken($this->token)
            ->getJson('/api/tenant/maintenance')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_submit_maintenance_request_via_api(): void
    {
        $room  = Room::factory()->create();
        Lease::factory()->create(['occupant_id' => $this->occupant->id, 'room_id' => $room->id, 'status' => 'active']);

        $this->withToken($this->token)
            ->postJson('/api/tenant/maintenance', [
                'title'       => 'Pintu Rusak',
                'description' => 'Engsel pintu lepas.',
                'priority'    => 'high',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Laporan berhasil dikirim.');

        $this->assertDatabaseHas('maintenance_requests', [
            'occupant_id' => $this->occupant->id,
            'title'       => 'Pintu Rusak',
        ]);
    }

    public function test_maintenance_api_validates_required_fields(): void
    {
        $this->withToken($this->token)
            ->postJson('/api/tenant/maintenance', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);
    }
}
