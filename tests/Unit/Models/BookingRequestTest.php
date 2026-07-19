<?php

namespace Tests\Unit\Models;

use App\Models\BookingRequest;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    public function test_factory_creates_booking_request(): void
    {
        $booking = BookingRequest::factory()->create();
        $this->assertDatabaseHas('booking_requests', ['id' => $booking->id, 'status' => 'pending']);
    }

    public function test_status_color_helpers(): void
    {
        $booking = BookingRequest::factory()->make(['status' => 'pending']);
        $this->assertEquals('warning', $booking->status_color);

        $booking->status = 'approved';
        $this->assertEquals('success', $booking->status_color);

        $booking->status = 'rejected';
        $this->assertEquals('danger', $booking->status_color);
    }

    public function test_status_label_helper(): void
    {
        $booking = BookingRequest::factory()->make(['status' => 'contacted']);
        $this->assertEquals('Dihubungi', $booking->status_label);

        $booking->status = 'converted';
        $this->assertEquals('Jadi Penyewa', $booking->status_label);
    }

    public function test_belongs_to_property(): void
    {
        $booking = BookingRequest::factory()->create();
        $this->assertNotNull($booking->property);
    }
}
