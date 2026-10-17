<?php

namespace Tests\Unit\Services;

use App\Models\Deposit;
use App\Models\Lease;
use App\Models\Room;
use App\Services\CheckInOutService;
use App\Services\DepositService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckInOutServiceTest extends TestCase
{
    use RefreshDatabase;

    private function activeLease(): Lease
    {
        $room = Room::factory()->create(['status' => 'available']);
        $lease = Lease::factory()->create(['room_id' => $room->id, 'status' => 'active']);

        return $lease;
    }

    public function test_check_in_marks_room_occupied_and_rejects_duplicate(): void
    {
        $lease = $this->activeLease();
        $service = app(CheckInOutService::class);

        $record = $service->checkIn($lease, ['meter_electric' => 100, 'meter_water' => 20]);

        $this->assertSame('check_in', $record->type);
        $this->assertSame('occupied', $lease->room->fresh()->status);

        $this->expectException(ValidationException::class);
        $service->checkIn($lease->fresh());
    }

    public function test_check_out_without_check_in_is_rejected(): void
    {
        $lease = $this->activeLease();

        $this->expectException(ValidationException::class);
        app(CheckInOutService::class)->checkOut($lease);
    }

    public function test_check_out_ends_lease_settles_deposit_and_frees_room(): void
    {
        $lease = $this->activeLease();
        $service = app(CheckInOutService::class);
        $service->checkIn($lease);

        Deposit::create([
            'lease_id' => $lease->id,
            'tenant_id' => $lease->occupant_id,
            'amount' => 1000000,
            'status' => 'pending',
            'type' => 'security',
        ]);
        $deposit = Deposit::where('lease_id', $lease->id)->first();
        app(DepositService::class)->markReceived($deposit);

        $record = $service->checkOut($lease->fresh(), [
            'damage_amount' => 200000,
            'cleaning_amount' => 50000,
            'execute_settlement' => true,
        ]);

        $this->assertSame('ended', $lease->fresh()->status);
        $this->assertContains($lease->room->fresh()->status, ['cleaning', 'inspection']);
        // 250rb dipotong (kerusakan+cleaning), sisa 750rb direfund ke tenant.
        $this->assertSame('refunded', $deposit->fresh()->status);
        $this->assertSame(750000.0, (float) $deposit->fresh()->refunded_amount);
        $this->assertSame(250000.0, (float) $record->deposit_deduction);
    }

    public function test_check_out_rejects_negative_amounts(): void
    {
        $lease = $this->activeLease();
        $service = app(CheckInOutService::class);
        $service->checkIn($lease);

        $this->expectException(ValidationException::class);
        $service->checkOut($lease->fresh(), ['damage_amount' => -100]);
    }

    public function test_check_in_draft_lease_is_rejected(): void
    {
        $room = Room::factory()->create(['status' => 'available']);
        $lease = Lease::factory()->create(['room_id' => $room->id, 'status' => 'draft']);

        $this->expectException(HttpException::class);
        app(CheckInOutService::class)->checkIn($lease);
    }
}
