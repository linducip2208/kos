<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature   = 'invoices:mark-overdue';
    protected $description = 'Tandai invoice yang melewati jatuh tempo dan hitung denda';

    public function handle(): int
    {
        $invoices = Invoice::whereIn('status', ['sent'])
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            $penalty = $invoice->calculatePenalty();
            $invoice->update([
                'status'  => 'overdue',
                'penalty' => $penalty,
            ]);
            $count++;
        }

        $this->info("Invoice ditandai overdue: {$count}");
        return self::SUCCESS;
    }
}
