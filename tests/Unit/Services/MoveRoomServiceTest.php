<?php

namespace Tests\Unit\Services;

use App\Models\Lease;
use App\Models\Room;
use App\Services\MoveRoomService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MoveRoomServiceTest extends TestCase
{
    use RefreshDatabase;

    private function activeLease(Room $room): Lease
    {
        return Lease::factory()->create(['room_id' => $room->id, 'status' => 'active']);
    }

    public function test_transfer_to_cheaper_room_creates_credit_invoice(): void
    {
        $from = Room::factory()->create(['status' => 'occupied', 'price_monthly' => 1500000]);
        $to = Room::factory()->create(['status' => 'available', 'price_monthly' => 500000]);
        $lease = $this->activeLease($from);

        $transfer = app(MoveRoomService::class)->transfer($lease, $to);

        $this->assertSame('completed', $transfer->status);
        $this->assertSame($to->id, $lease->fresh()->room_id);
        $this->assertSame('cleaning', $from->fresh()->status);
        $this->assertSame('occupied', $to->fresh()->status);

        $invoice = $lease->invoices()->where('notes', 'Auto-generated oleh Move Room')->firstOrFail();
        $this->assertLessThan(0, (float) $invoice->total);
        $this->assertStringContainsString('Kredit', $invoice->additional_charges[0]['label']);
    }

    public function test_transfer_to_expensive_room_creates_charge_invoice(): void
    {
        $from = Room::factory()->create(['status' => 'occupied', 'price_monthly' => 500000]);
        $to = Room::factory()->create(['status' => 'available', 'price_monthly' => 1500000]);
        $lease = $this->activeLease($from);

        app(MoveRoomService::class)->transfer($lease, $to);

        $invoice = $lease->invoices()->where('notes', 'Auto-generated oleh Move Room')->firstOrFail();
        $this->assertGreaterThan(0, (float) $invoice->total);
    }

    public function test_transfer_ended_lease_is_rejected(): void
    {
        $from = Room::factory()->create(['status' => 'occupied']);
        $to = Room::factory()->create(['status' => 'available']);
        $lease = Lease::factory()->create(['room_id' => $from->id, 'status' => 'ended']);

        $this->expectException(HttpException::class);
        app(MoveRoomService::class)->transfer($lease, $to);
    }

    public function test_transfer_to_same_or_occupied_room_is_rejected(): void
    {
        $from = Room::factory()->create(['status' => 'occupied']);
        $lease = $this->activeLease($from);

        $this->expectException(HttpException::class);
        app(MoveRoomService::class)->transfer($lease, $from);

        $occupied = Room::factory()->create(['status' => 'occupied']);
        try {
            app(MoveRoomService::class)->transfer($lease->fresh(), $occupied);
            $this->fail('Transfer ke kamar occupied harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }
}
