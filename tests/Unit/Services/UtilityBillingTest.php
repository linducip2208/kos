<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Models\UtilityRate;
use App\Services\UtilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UtilityBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_reading_same_period_updates_in_place(): void
    {
        $room = Room::factory()->create();
        $service = app(UtilityService::class);

        $service->recordReading($room->id, 'electricity', 100, '2026-09');
        $service->recordReading($room->id, 'electricity', 150, '2026-10');
        $service->recordReading($room->id, 'electricity', 180, '2026-10');

        $this->assertSame(1, $room->utilityReadings()->whereDate('billing_period', '2026-10-01')->count());
        $reading = $room->utilityReadings()->whereDate('billing_period', '2026-10-01')->first();
        $this->assertSame(100.0, (float) $reading->previous_reading);
        $this->assertSame(80.0, (float) $reading->usage);
    }

    public function test_zero_usage_reading_has_zero_amount(): void
    {
        $room = Room::factory()->create();
        $reading = app(UtilityService::class)->recordReading($room->id, 'water', 0, '2026-10');

        $this->assertSame(0.0, (float) $reading->amount);
    }

    public function test_meter_reset_is_rejected(): void
    {
        $room = Room::factory()->create();
        $service = app(UtilityService::class);
        $service->recordReading($room->id, 'electricity', 200, '2026-09');

        $this->expectException(ValidationException::class);
        $service->recordReading($room->id, 'electricity', 150, '2026-10');
    }

    public function test_historical_invoice_immutable_when_rate_changes(): void
    {
        $room = Room::factory()->create();
        $lease = Lease::factory()->create(['room_id' => $room->id]);
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000, 'base_amount' => 1000000]);

        UtilityRate::create([
            'scope' => 'global', 'utility_type' => 'electricity',
            'rate_per_unit' => 2000, 'is_active' => true,
        ]);

        $service = app(UtilityService::class);
        $reading = $service->recordReading($room->id, 'electricity', 100, '2026-10');
        $service->addToInvoice($reading, $invoice);
        $oldTotal = (float) $invoice->fresh()->total;

        // Tarif berubah — invoice lama tidak boleh ikut berubah.
        UtilityRate::where('utility_type', 'electricity')->update(['rate_per_unit' => 5000]);
        $newReading = $service->recordReading($room->id, 'electricity', 150, '2026-11');

        $this->assertSame($oldTotal, (float) $invoice->fresh()->total);
        $this->assertSame(250000.0, (float) $newReading->amount); // 50 unit × 5000
    }

    public function test_add_to_invoice_twice_is_rejected(): void
    {
        $room = Room::factory()->create();
        $lease = Lease::factory()->create(['room_id' => $room->id]);
        $service = app(UtilityService::class);
        $reading = $service->recordReading($room->id, 'electricity', 100, '2026-10');
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000, 'base_amount' => 1000000]);
        $service->addToInvoice($reading, $invoice);

        $invoice2 = Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1000000, 'base_amount' => 1000000]);
        $this->expectException(ValidationException::class);
        $service->addToInvoice($reading->fresh(), $invoice2);
    }
}
