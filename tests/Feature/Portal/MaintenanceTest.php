<?php

namespace Tests\Feature\Portal;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Room;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    public function test_maintenance_list_requires_auth(): void
    {
        $this->get(route('portal.maintenance.index'))->assertRedirect(route('portal.login'));
    }

    public function test_maintenance_list_shows_own_requests(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        MaintenanceRequest::factory()->create(['occupant_id' => $occupant->id, 'title' => 'AC Rusak']);

        $other = Occupant::factory()->withPortalAccess()->create();
        MaintenanceRequest::factory()->create(['occupant_id' => $other->id, 'title' => 'Kran Bocor']);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.index'))
            ->assertOk()
            ->assertSee('AC Rusak')
            ->assertDontSee('Kran Bocor');
    }

    public function test_create_form_is_accessible(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.create'))
            ->assertOk();
    }

    public function test_can_submit_maintenance_request(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $room     = Room::factory()->create();
        Lease::factory()->create(['occupant_id' => $occupant->id, 'room_id' => $room->id, 'status' => 'active']);

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [
                'title'       => 'Lampu mati',
                'description' => 'Lampu kamar tiba-tiba mati sejak kemarin.',
                'priority'    => 'medium',
            ])
            ->assertRedirect(route('portal.maintenance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_requests', [
            'occupant_id' => $occupant->id,
            'title'       => 'Lampu mati',
            'status'      => 'open',
            'priority'    => 'medium',
        ]);
    }

    public function test_maintenance_store_validates_required_fields(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [])
            ->assertSessionHasErrors(['title', 'description', 'priority']);
    }
}
