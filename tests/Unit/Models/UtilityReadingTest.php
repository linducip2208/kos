<?php

namespace Tests\Unit\Models;

use App\Models\UtilityReading;
use Tests\TestCase;

class UtilityReadingTest extends TestCase
{
    public function test_usage_is_calculated_correctly(): void
    {
        $reading = UtilityReading::factory()->create([
            'previous_reading' => 100,
            'current_reading'  => 175,
            'rate_per_unit'    => 1500,
            'amount'           => 112500,
        ]);

        // virtualAs column from DB
        $fresh = $reading->fresh();
        $this->assertEquals(75, (float) $fresh->usage);
    }

    public function test_type_label_attribute(): void
    {
        $electricity = UtilityReading::factory()->create(['type' => 'electricity']);
        $water       = UtilityReading::factory()->create(['type' => 'water', 'billing_period' => now()->subMonth()->startOfMonth()->toDateString()]);
        $gas         = UtilityReading::factory()->create(['type' => 'gas', 'billing_period' => now()->subMonths(2)->startOfMonth()->toDateString()]);

        $this->assertEquals('Listrik', $electricity->type_label);
        $this->assertEquals('Air',     $water->type_label);
        $this->assertEquals('Gas',     $gas->type_label);
    }

    public function test_factory_creates_valid_record(): void
    {
        $reading = UtilityReading::factory()->create();
        $this->assertDatabaseHas('utility_readings', ['id' => $reading->id]);
        $this->assertGreaterThan(0, $reading->amount);
    }
}
