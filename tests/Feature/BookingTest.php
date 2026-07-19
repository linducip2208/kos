<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_booking_page_shows_property(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Melati Indah']);

        $this->get(route('booking.show', $property))
            ->assertOk()
            ->assertSee('Kos Melati Indah');
    }

    public function test_booking_page_shows_available_rooms(): void
    {
        $property = Property::factory()->create();
        Room::factory()->available()->create(['property_id' => $property->id, 'room_number' => 'C3']);
        Room::factory()->occupied()->create(['property_id' => $property->id, 'room_number' => 'C4']);

        $this->get(route('booking.show', $property))
            ->assertSee('C3')
            ->assertDontSee('C4');
    }

    public function test_can_submit_booking_request(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [
            'name'            => 'Siti Rahayu',
            'phone'           => '081234567890',
            'email'           => 'siti@example.com',
            'desired_move_in' => now()->addDays(14)->format('Y-m-d'),
            'billing_cycle'   => 'monthly',
        ])->assertRedirect()
          ->assertSessionHas('booking_success');

        $this->assertDatabaseHas('booking_requests', [
            'property_id' => $property->id,
            'name'        => 'Siti Rahayu',
            'status'      => 'pending',
        ]);
    }

    public function test_booking_validates_required_fields(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [])
            ->assertSessionHasErrors(['name', 'phone', 'desired_move_in', 'billing_cycle']);
    }

    public function test_booking_move_in_must_be_in_future(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [
            'name'            => 'Test User',
            'phone'           => '081111111111',
            'desired_move_in' => now()->subDay()->format('Y-m-d'),
            'billing_cycle'   => 'monthly',
        ])->assertSessionHasErrors('desired_move_in');
    }

    public function test_success_banner_shown_after_booking(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [
            'name'            => 'Ahmad Fauzi',
            'phone'           => '082222222222',
            'desired_move_in' => now()->addDays(7)->format('Y-m-d'),
            'billing_cycle'   => 'monthly',
        ])->assertRedirect();

        $this->get(route('booking.show', $property))
            ->assertSee('Permintaan Booking Terkirim');
    }
}
