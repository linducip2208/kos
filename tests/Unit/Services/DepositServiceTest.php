<?php

namespace Tests\Unit\Services;

use App\Models\Deposit;
use App\Models\Occupant;
use App\Services\DepositService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DepositServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_received_deposit_is_recorded_in_ledger(): void
    {
        $deposit = Deposit::create([
            'tenant_id' => Occupant::factory()->create()->id,
            'amount' => 1500000,
            'status' => 'pending',
            'type' => 'security',
        ]);

        app(DepositService::class)->markReceived($deposit);

        $this->assertSame(1500000.0, (float) $deposit->refresh()->balance);
        $this->assertSame('held', $deposit->status);
        $this->assertDatabaseHas('deposit_transactions', [
            'deposit_id' => $deposit->id,
            'type' => 'receipt',
            'amount' => 1500000,
        ]);
    }

    public function test_deduction_cannot_exceed_deposit_balance(): void
    {
        $deposit = Deposit::create([
            'tenant_id' => Occupant::factory()->create()->id,
            'amount' => 500000,
            'status' => 'held',
        ]);

        $this->expectException(HttpException::class);

        app(DepositService::class)->deduct($deposit, 500001, 'Kerusakan');
    }
}
