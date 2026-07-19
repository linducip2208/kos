<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->invoice_number)) {
            $prefix = setting('invoice_prefix', 'INV');
            $year   = now()->format('Y');
            $month  = now()->format('m');
            $last   = Invoice::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count() + 1;
            $invoice->invoice_number = $prefix . '/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
        }
    }
}
