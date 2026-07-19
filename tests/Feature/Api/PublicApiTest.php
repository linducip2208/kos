<?php

namespace Tests\Feature\Api;

use App\Models\Property;
use App\Models\Room;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    public function test_properties_list_is_public(): void
    {
        Property::factory()->count(3)->create(['is_active' => true]);
        Property::factory()->create(['is_active' => false]);

        $this->getJson('/api/properties')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_property_show_includes_available_rooms(): void
    {
        $property = Property::factory()->create();
        Room::factory()->available()->count(2)->create(['property_id' => $property->id]);
        Room::factory()->occupied()->count(3)->create(['property_id' => $property->id]);

        $this->getJson("/api/properties/{$property->id}")
            ->assertOk()
            ->assertJsonCount(2, 'rooms')
            ->assertJsonPath('name', $property->name);
    }

    public function test_property_show_has_booking_url(): void
    {
        $property = Property::factory()->create();

        $this->getJson('/api/properties')
            ->assertOk()
            ->assertJsonFragment(['booking_url' => url("/booking/{$property->id}")]);
    }
}
