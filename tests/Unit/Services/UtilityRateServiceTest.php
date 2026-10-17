<?php

namespace Tests\Unit\Services;

use App\Models\Property;
use App\Models\Room;
use App\Models\UtilityRate;
use App\Services\UtilityRateService;
use App\Services\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtilityRateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tiered_rate_prefers_most_specific_scope(): void
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);

        UtilityRate::create([
            'scope' => 'global', 'utility_type' => 'electricity',
            'rate_per_unit' => 1000, 'is_active' => true,
        ]);
        UtilityRate::create([
            'scope' => 'property', 'property_id' => $property->id,
            'utility_type' => 'electricity', 'rate_per_unit' => 1500, 'is_active' => true,
        ]);
        UtilityRate::create([
            'scope' => 'room', 'room_id' => $room->id,
            'utility_type' => 'electricity', 'rate_per_unit' => 2000, 'is_active' => true,
        ]);

        $service = app(UtilityRateService::class);
        $this->assertSame(2000.0, $service->ratePerUnit($room->fresh(), 'electricity'));

        // Tarif kamar lain di properti sama → tarif properti.
        $other = Room::factory()->create(['property_id' => $property->id]);
        $this->assertSame(1500.0, $service->ratePerUnit($other, 'electricity'));
    }

    public function test_utility_service_uses_tiered_rate_when_available(): void
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        UtilityRate::create([
            'scope' => 'property', 'property_id' => $property->id,
            'utility_type' => 'electricity', 'rate_per_unit' => 2500, 'is_active' => true,
        ]);

        $service = app(UtilityService::class);
        $reading = $service->recordReading($room->id, 'electricity', 100, '2026-10');

        $this->assertSame(2500.0, (float) $reading->rate_per_unit);
        $this->assertSame(250000.0, (float) $reading->amount);
    }

    public function test_calculate_applies_minimum_usage_and_charges(): void
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);
        UtilityRate::create([
            'scope' => 'property', 'property_id' => $property->id,
            'utility_type' => 'water', 'rate_per_unit' => 5000,
            'minimum_usage' => 10, 'fixed_charge' => 10000, 'admin_charge' => 2500,
            'is_active' => true,
        ]);

        $result = app(UtilityRateService::class)->calculate($room, 'water', 4);

        $this->assertSame(10.0, $result['billable_usage']);
        $this->assertSame(62500.0, $result['total']); // 10*5000 + 10000 + 2500
    }
}
