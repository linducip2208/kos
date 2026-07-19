<?php

namespace App\Observers;

use App\Models\Lease;

class LeaseObserver
{
    public function creating(Lease $lease): void
    {
        if (empty($lease->lease_number)) {
            $prefix  = setting('lease_prefix', 'KTR');
            $year    = now()->format('Y');
            $month   = now()->format('m');
            $last    = Lease::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count() + 1;
            $lease->lease_number = $prefix . '/' . $year . '/' . $month . '/' . str_pad($last, 4, '0', STR_PAD_LEFT);
        }
    }
}
