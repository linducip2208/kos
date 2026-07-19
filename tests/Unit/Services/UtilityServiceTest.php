<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use App\Models\UtilityReading;
use App\Services\UtilityService;
use Tests\TestCase;

class UtilityServiceTest extends TestCase
{
    private UtilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UtilityService::class);
    }

    public function test_get_default_rate_electricity(): void
    {
        $rate = $this->service->getDefaultRate('electricity');
        $this->assertGreaterThan(0, $rate);
    }

    public function test_get_default_rate_water(): void
    {
        $rate = $this->service->getDefaultRate('water');
        $this->assertGreaterThan(0, $rate);
    }

    public function test_record_reading_creates_record(): void
    {
        $room = Room::factory()->create();

        $reading = $this->service->recordReading($room->id, 'electricity', 150, now()->startOfMonth()->toDateString(), 1500);

        $this->assertDatabaseHas('utility_readings', [
            'room_id'         => $room->id,
            'type'            => 'electricity',
            'current_reading' => 150,
        ]);
        $this->assertEquals(0, $reading->previous_reading);
    }

    public function test_record_reading_uses_previous_reading(): void
    {
        $room = Room::factory()->create();

        UtilityReading::factory()->create([
            'room_id'          => $room->id,
            'type'             => 'electricity',
            'current_reading'  => 100,
            'previous_reading' => 50,
            'billing_period'   => now()->subMonth()->startOfMonth()->toDateString(),
            'reading_date'     => now()->subMonth()->toDateString(),
            'rate_per_unit'    => 1500,
            'amount'           => 75000,
        ]);

        $reading = $this->service->recordReading($room->id, 'electricity', 160, now()->startOfMonth()->toDateString(), 1500);

        $this->assertEquals(100, $reading->previous_reading);
        $this->assertEquals(160, $reading->current_reading);
    }

    public function test_add_to_invoice_marks_reading(): void
    {
        $property = Property::factory()->create();
        $room     = Room::factory()->create(['property_id' => $property->id]);
        $occupant = Occupant::factory()->create();
        $lease    = Lease::factory()->create(['room_id' => $room->id, 'occupant_id' => $occupant->id]);
        $invoice  = Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'draft']);

        $reading = UtilityReading::factory()->create([
            'room_id'        => $room->id,
            'lease_id'       => $lease->id,
            'added_to_invoice' => false,
        ]);

        $this->service->addToInvoice($reading, $invoice);

        $this->assertTrue($reading->fresh()->added_to_invoice);
        $this->assertEquals($invoice->id, $reading->fresh()->invoice_id);
    }
}
