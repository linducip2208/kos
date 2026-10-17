<?php

namespace Tests\Unit\Services;

use App\Models\Deposit;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use App\Services\DepositService;
use App\Services\LeaseWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DepositLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function heldDeposit(float $amount = 1000000): Deposit
    {
        $deposit = Deposit::create([
            'tenant_id' => Occupant::factory()->create()->id,
            'amount' => $amount,
            'status' => 'pending',
            'type' => 'security',
        ]);

        return app(DepositService::class)->markReceived($deposit);
    }

    public function test_deduct_full_balance_marks_partially_used_with_zero_balance(): void
    {
        $deposit = $this->heldDeposit();
        app(DepositService::class)->deduct($deposit, 1000000, 'Kerusakan berat');

        $this->assertSame(0.0, (float) $deposit->refresh()->balance);
        $this->assertSame('partially_used', $deposit->status);
    }

    public function test_second_full_deduct_is_rejected(): void
    {
        $deposit = $this->heldDeposit();
        $service = app(DepositService::class);
        $service->deduct($deposit, 1000000, 'Kerusakan');

        $this->expectException(HttpException::class);
        $service->deduct($deposit->refresh(), 1000000, 'Kerusakan lagi');
    }

    public function test_partial_refund_keeps_deposit_open_and_second_partial_allowed(): void
    {
        $deposit = $this->heldDeposit();
        $service = app(DepositService::class);

        $service->refund($deposit, 400000);
        $this->assertSame('partially_used', $deposit->refresh()->status);
        $this->assertSame(600000.0, (float) $deposit->balance);

        $service->refund($deposit->refresh(), 600000);
        $this->assertSame('refunded', $deposit->refresh()->status);
        $this->assertSame(0.0, (float) $deposit->balance);
    }

    public function test_refund_after_full_refund_is_rejected(): void
    {
        $deposit = $this->heldDeposit();
        $service = app(DepositService::class);
        $service->refund($deposit);

        $this->expectException(HttpException::class);
        $service->refund($deposit->refresh(), 1000);
    }

    public function test_mark_received_on_settled_deposit_is_rejected(): void
    {
        $deposit = $this->heldDeposit();
        $service = app(DepositService::class);
        $service->forfeit($deposit, 'Hangus');

        $this->expectException(HttpException::class);
        $service->markReceived($deposit->refresh());
    }

    public function test_forfeit_twice_is_rejected(): void
    {
        $deposit = $this->heldDeposit();
        $service = app(DepositService::class);
        $service->forfeit($deposit, 'Hangus');

        $this->expectException(HttpException::class);
        $service->forfeit($deposit->refresh(), 'Hangus lagi');
    }

    public function test_terminate_draft_lease_is_rejected_instead_of_silent_ended(): void
    {
        $lease = Lease::factory()->create(['status' => 'draft']);

        $this->expectException(ValidationException::class);
        app(LeaseWorkflowService::class)->terminate($lease, 'Coba terminasi draft');
    }

    public function test_terminate_active_lease_sets_terminated_and_frees_room(): void
    {
        $room = Room::factory()->occupied()->create();
        $lease = Lease::factory()->create(['room_id' => $room->id, 'status' => 'active']);

        $result = app(LeaseWorkflowService::class)->terminate($lease, 'Pemutusan kontrak');

        $this->assertSame('terminated', $result->status);
        $this->assertSame('cleaning', $room->fresh()->status);
    }

    public function test_give_notice_on_ended_lease_is_rejected(): void
    {
        $lease = Lease::factory()->create(['status' => 'ended']);

        $this->expectException(ValidationException::class);
        app(LeaseWorkflowService::class)->giveNotice($lease);
    }

    public function test_renew_draft_lease_is_rejected(): void
    {
        $lease = Lease::factory()->create(['status' => 'draft']);

        $this->expectException(ValidationException::class);
        app(LeaseWorkflowService::class)->renew($lease);
    }
}
