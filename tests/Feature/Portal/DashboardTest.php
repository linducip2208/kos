<?php

namespace Tests\Feature\Portal;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_renders_for_authenticated_occupant(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertViewIs('portal.dashboard.index');
    }

    public function test_dashboard_shows_active_lease(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Maju Jaya']);
        $room     = Room::factory()->create(['property_id' => $property->id, 'room_number' => 'A1']);
        $occupant = Occupant::factory()->withPortalAccess()->create();
        Lease::factory()->create(['room_id' => $room->id, 'occupant_id' => $occupant->id, 'status' => 'active']);

        $response = $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'));

        $response->assertSee('Kos Maju Jaya');
        $response->assertSee('A1');
    }

    public function test_dashboard_shows_unpaid_invoice_count(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent']);
        Invoice::factory()->overdue()->create(['lease_id' => $lease->id]);

        $response = $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'));

        $response->assertSee('2');
    }

    public function test_dashboard_shows_open_maintenance_count(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        MaintenanceRequest::factory()->create(['occupant_id' => $occupant->id, 'status' => 'open']);
        MaintenanceRequest::factory()->create(['occupant_id' => $occupant->id, 'status' => 'in_progress']);
        MaintenanceRequest::factory()->create(['occupant_id' => $occupant->id, 'status' => 'resolved']);

        $response = $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'));

        $response->assertOk();
    }
}
