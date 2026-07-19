<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::where('status', 'active')->get();
        $inv    = 1;

        foreach ($leases as $lease) {
            // Buat 3 bulan tagihan ke belakang per kontrak
            for ($m = 3; $m >= 1; $m--) {
                $periodStart = now()->subMonths($m)->startOfMonth();
                $periodEnd   = $periodStart->copy()->endOfMonth();
                $dueDate     = $periodStart->copy()->addDays(10);

                // Bulan 3 ke belakang = lunas, bulan 2 ke belakang = lunas, bulan lalu = sent/overdue
                if ($m >= 2) {
                    $status = 'paid';
                    $paidAt = $dueDate->copy()->subDays(rand(1, 5));
                } else {
                    // Bulan terakhir: beberapa sudah bayar, beberapa belum, satu overdue
                    $status = match ($inv % 4) {
                        0 => 'paid',
                        1 => 'sent',
                        2 => 'overdue',
                        default => 'sent',
                    };
                    $paidAt = $status === 'paid' ? $dueDate->copy() : null;
                }

                Invoice::create([
                    'lease_id'       => $lease->id,
                    'invoice_number' => 'INV-' . str_pad($inv, 5, '0', STR_PAD_LEFT),
                    'period_start'   => $periodStart->toDateString(),
                    'period_end'     => $periodEnd->toDateString(),
                    'due_date'       => $dueDate->toDateString(),
                    'base_amount'    => $lease->price,
                    'total'          => $lease->price,
                    'status'         => $status,
                    'paid_at'        => $paidAt,
                    'payment_method' => $status === 'paid' ? 'transfer' : null,
                    'sent_at'        => now()->subDays(rand(5, 10)),
                ]);

                $inv++;
            }

            // Tagihan bulan ini (draft / sent)
            $thisMonth = now()->startOfMonth();
            Invoice::create([
                'lease_id'       => $lease->id,
                'invoice_number' => 'INV-' . str_pad($inv, 5, '0', STR_PAD_LEFT),
                'period_start'   => $thisMonth->toDateString(),
                'period_end'     => $thisMonth->copy()->endOfMonth()->toDateString(),
                'due_date'       => $thisMonth->copy()->addDays(10)->toDateString(),
                'base_amount'    => $lease->price,
                'total'          => $lease->price,
                'status'         => 'sent',
                'sent_at'        => now()->subDays(2),
            ]);
            $inv++;
        }
    }
}
