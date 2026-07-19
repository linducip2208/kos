<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Lease;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'invoices:generate {--dry-run : Preview without creating}';
    protected $description = 'Generate tagihan otomatis untuk kontrak aktif yang sudah jatuh tempo tagih';

    public function handle(): int
    {
        $today  = Carbon::today();
        $leases = Lease::with(['room', 'occupant'])
            ->where('status', 'active')
            ->get();

        $created = 0;
        foreach ($leases as $lease) {
            if (!$this->shouldGenerate($lease, $today)) continue;

            [$periodStart, $periodEnd, $dueDate] = $this->getPeriod($lease, $today);

            $exists = Invoice::where('lease_id', $lease->id)
                ->where('period_start', $periodStart)
                ->exists();
            if ($exists) continue;

            if ($this->option('dry-run')) {
                $this->line("  [DRY] {$lease->occupant->name} - {$lease->room->room_number} - {$periodStart->format('M Y')}");
            } else {
                Invoice::create([
                    'lease_id'     => $lease->id,
                    'period_start' => $periodStart,
                    'period_end'   => $periodEnd,
                    'due_date'     => $dueDate,
                    'base_amount'  => $lease->price,
                    'total'        => $lease->price,
                    'status'       => 'sent',
                ]);
            }
            $created++;
        }

        $this->info("Tagihan dibuat: {$created}");
        return self::SUCCESS;
    }

    private function shouldGenerate(Lease $lease, Carbon $today): bool
    {
        $billingDate = (int) ($lease->billing_date ?? 1);
        return $today->day === $billingDate;
    }

    private function getPeriod(Lease $lease, Carbon $today): array
    {
        $billingDate  = (int) ($lease->billing_date ?? 1);
        $periodStart  = $today->copy()->startOfMonth()->addDays($billingDate - 1);

        $periodEnd = match ($lease->billing_cycle) {
            'quarterly' => $periodStart->copy()->addMonths(3)->subDay(),
            'yearly'    => $periodStart->copy()->addYear()->subDay(),
            default     => $periodStart->copy()->addMonth()->subDay(),
        };

        $reminderDays = (int) setting('reminder_days', 3);
        $dueDate      = $periodStart->copy()->addDays($reminderDays);

        return [$periodStart, $periodEnd, $dueDate];
    }
}
