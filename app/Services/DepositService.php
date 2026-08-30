<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\DepositTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Ledger deposit: penerimaan, potongan, refund, hangus.
 * Semua mutasi tercatat dengan saldo berjalan (row-lock + DB transaction).
 */
class DepositService
{
    /**
     * Tandai deposit diterima → status held + ledger receipt.
     */
    public function markReceived(Deposit $deposit, ?string $method = 'cash', ?string $reference = null): Deposit
    {
        return DB::transaction(function () use ($deposit, $method, $reference) {
            $deposit = Deposit::lockForUpdate()->find($deposit->id);

            if ($deposit->status === 'pending') {
                $this->seedLedgerIfNeeded($deposit);
                $balance = round((float) $deposit->balance, 2);

                $this->addTransaction($deposit, 'receipt', (float) $deposit->amount, [
                    'method' => $method,
                    'reference' => $reference,
                    'reason' => 'Penerimaan deposit',
                    'balance_after' => round($balance + (float) $deposit->amount, 2),
                ]);

                $deposit->forceFill(['paid_at' => $deposit->paid_at ?? today()])->save();
            }

            $deposit->forceFill(['status' => 'held'])->save();

            return $deposit->refresh();
        });
    }

    /**
     * Potong deposit (damage, cleaning, utilitas, denda).
     */
    public function deduct(Deposit $deposit, float $amount, string $reason, array $options = []): DepositTransaction
    {
        return DB::transaction(function () use ($deposit, $amount, $reason, $options) {
            $deposit = Deposit::lockForUpdate()->find($deposit->id);

            if ($deposit->is_settled) {
                abort(422, 'Deposit sudah diselesaikan.');
            }

            $this->seedLedgerIfNeeded($deposit);

            $amount = round($amount, 2);

            if ($amount <= 0) {
                abort(422, 'Nominal potongan harus lebih besar dari nol.');
            }

            if ($amount > (float) $deposit->balance + 0.009) {
                abort(422, 'Nominal potongan melebihi saldo deposit.');
            }

            $tx = $this->addTransaction($deposit, 'deduction', $amount, [
                'reason' => $reason,
                'source_type' => $options['source_type'] ?? null,
                'source_id' => $options['source_id'] ?? null,
                'balance_after' => round((float) $deposit->balance - $amount, 2),
            ]);

            $remaining = round((float) $deposit->refresh()->balance, 2);

            if (! in_array($deposit->status, ['refunded', 'forfeited'], true)) {
                $deposit->update([
                    'status' => $remaining <= 0.009 ? 'partially_used' : 'partially_used',
                ]);
            }

            return $tx;
        });
    }

    /**
     * Refund sisa deposit ke tenant.
     */
    public function refund(Deposit $deposit, ?float $amount = null, ?string $method = 'transfer', string $reason = 'Refund deposit akhir sewa'): Deposit
    {
        return DB::transaction(function () use ($deposit, $amount, $method, $reason) {
            $deposit = Deposit::lockForUpdate()->find($deposit->id);

            $this->seedLedgerIfNeeded($deposit);

            $balance = round((float) $deposit->balance, 2);
            $refundAmount = round($amount ?? $balance, 2);

            if ($refundAmount <= 0 || $refundAmount > $balance + 0.009) {
                abort(422, 'Nominal refund melebihi saldo deposit.');
            }

            $this->addTransaction($deposit, 'refund', $refundAmount, [
                'reason' => $reason,
                'method' => $method,
                'balance_after' => round($balance - $refundAmount, 2),
            ]);

            $deposit->forceFill([
                'status' => 'refunded',
                'refunded_amount' => (float) $deposit->refunded_amount + $refundAmount,
                'refunded_at' => today(),
            ])->save();

            return $deposit->refresh();
        });
    }

    /**
     * Hanguskan deposit.
     */
    public function forfeit(Deposit $deposit, string $reason): Deposit
    {
        return DB::transaction(function () use ($deposit, $reason) {
            $deposit = Deposit::lockForUpdate()->find($deposit->id);

            $this->seedLedgerIfNeeded($deposit);

            $balance = round((float) $deposit->balance, 2);

            if ($balance > 0) {
                $this->addTransaction($deposit, 'forfeit', $balance, [
                    'reason' => $reason,
                    'balance_after' => 0,
                ]);
            }

            $deposit->forceFill(['status' => 'forfeited'])->save();

            return $deposit->refresh();
        });
    }

    /**
     * Settlement checkout: hitung seluruh kewajiban tenant vs deposit.
     */
    public function buildCheckoutSettlement(Deposit $deposit): array
    {
        $lease = $deposit->lease;

        $outstandingRent = 0.0;
        $penalty = 0.0;

        if ($lease) {
            $lease->invoices()
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->get()
                ->each(function (Invoice $inv) use (&$outstandingRent, &$penalty) {
                    $outstandingRent += max(0, $inv->balance_due);
                    $penalty += (float) $inv->calculatePenalty();
                });
        }

        $utility = (float) ($lease?->utilityReadings()->where('added_to_invoice', false)->sum('amount') ?? 0);

        $checkout = $lease?->checkinRecords()->where('type', 'check_out')->orderByDesc('id')->first();
        $damage = (float) ($checkout?->damage_amount ?? 0);
        $cleaning = (float) ($checkout?->cleaning_amount ?? 0);

        $totalDue = round($outstandingRent + $utility + $damage + $cleaning + $penalty, 2);
        $depositBalance = round((float) $deposit->balance, 2);
        $deduction = min($depositBalance, $totalDue);
        $netDue = round($totalDue - $deduction, 2);

        return [
            'outstanding_rent' => $outstandingRent,
            'utility' => $utility,
            'damage' => $damage,
            'cleaning' => $cleaning,
            'penalty' => $penalty,
            'total_due' => $totalDue,
            'deposit_balance' => $depositBalance,
            'deduction' => $deduction,
            'tenant_payable' => max(0, $netDue),
            'tenant_refund' => round(max(0, $depositBalance - $deduction), 2),
        ];
    }

    /**
     * Eksekusi settlement hasil buildCheckoutSettlement().
     */
    public function executeCheckoutSettlement(Deposit $deposit, array $settlement): void
    {
        DB::transaction(function () use ($deposit, $settlement) {
            foreach ([
                ['key' => 'outstanding_rent', 'label' => 'Tunggakan sewa'],
                ['key' => 'utility',          'label' => 'Tagihan utilitas'],
                ['key' => 'damage',           'label' => 'Kerusakan'],
                ['key' => 'cleaning',         'label' => 'Biaya cleaning'],
                ['key' => 'penalty',          'label' => 'Denda keterlambatan'],
            ] as $part) {
                $amount = (float) ($settlement[$part['key']] ?? 0);
                if ($amount > 0) {
                    $this->deduct($deposit, $amount, $part['label']);
                }
            }

            $refund = (float) ($settlement['tenant_refund'] ?? 0);

            if ($refund > 0) {
                $this->refund($deposit, $refund, reason: 'Settlement checkout');
            } elseif (round((float) $deposit->refresh()->balance, 2) <= 0.009
                && ! in_array($deposit->status, ['forfeited'], true)) {
                $deposit->forceFill(['status' => 'partially_used'])->save();
            }
        });
    }

    /**
     * Seed ledger pertama kali dari saldo legacy (kolom lama).
     * Dipanggil sebelum mutasi apa pun agar saldo berjalan konsisten.
     */
    protected function seedLedgerIfNeeded(Deposit $deposit): void
    {
        if ($deposit->transactions()->lockForUpdate()->exists()) {
            return;
        }

        // Deposit pending belum punya uang masuk → ledger kosong, saldo 0.
        if ($deposit->status === 'pending') {
            return;
        }

        $legacyBalance = round((float) $deposit->amount - (float) $deposit->refunded_amount, 2);

        if ($legacyBalance <= 0) {
            return;
        }

        DepositTransaction::create([
            'deposit_id' => $deposit->id,
            'type' => 'receipt',
            'amount' => $legacyBalance,
            'reason' => 'Saldo awal (data lama)',
            'recorded_by' => auth()->id(),
            'occurred_at' => ($deposit->paid_at ?? today())->toDateString(),
            'balance_after' => $legacyBalance,
        ]);
    }

    protected function addTransaction(Deposit $deposit, string $type, float $amount, array $data = []): DepositTransaction
    {
        return DepositTransaction::create([
            'deposit_id' => $deposit->id,
            'type' => $type,
            'amount' => round($amount, 2),
            'reason' => $data['reason'] ?? null,
            'method' => $data['method'] ?? null,
            'reference' => $data['reference'] ?? null,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'recorded_by' => auth()->id(),
            'occurred_at' => today(),
            'balance_after' => $data['balance_after'] ?? round($deposit->balance, 2),
        ]);
    }
}
