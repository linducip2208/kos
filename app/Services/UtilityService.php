<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\UtilityReading;
use Carbon\Carbon;

class UtilityService
{
    public function getDefaultRate(string $type): float
    {
        return match ($type) {
            'electricity' => (float) setting('electricity_rate', 1352, 'utility'),
            'water'       => (float) setting('water_rate', 6000, 'utility'),
            'gas'         => (float) setting('gas_rate', 7000, 'utility'),
            default       => 0,
        };
    }

    public function recordReading(
        int $roomId,
        string $type,
        float $currentReading,
        string $billingPeriod,
        ?float $rateOverride = null
    ): UtilityReading {
        $previous = UtilityReading::where('room_id', $roomId)
            ->where('type', $type)
            ->where('billing_period', '<', $billingPeriod)
            ->orderByDesc('billing_period')
            ->value('current_reading') ?? 0;

        $rate   = $rateOverride ?? $this->getDefaultRate($type);
        $usage  = $currentReading - $previous;
        $amount = max(0, $usage) * $rate;

        $lease = Lease::where('room_id', $roomId)->where('status', 'active')->first();

        return UtilityReading::updateOrCreate(
            ['room_id' => $roomId, 'type' => $type, 'billing_period' => $billingPeriod],
            [
                'lease_id'         => $lease?->id,
                'previous_reading' => $previous,
                'current_reading'  => $currentReading,
                'rate_per_unit'    => $rate,
                'amount'           => $amount,
                'reading_date'     => now()->toDateString(),
            ]
        );
    }

    public function addToInvoice(UtilityReading $reading, Invoice $invoice): void
    {
        $charges   = $invoice->additional_charges ?? [];
        $label     = match ($reading->type) {
            'electricity' => 'Listrik',
            'water'       => 'Air',
            'gas'         => 'Gas',
            default       => ucfirst($reading->type),
        };

        $charges[] = ['label' => "{$label} ({$reading->usage} unit)", 'amount' => $reading->amount];

        $newTotal = $invoice->base_amount
            + collect($charges)->sum('amount')
            - $invoice->discount;

        $invoice->update([
            'additional_charges' => $charges,
            'total'              => $newTotal,
        ]);

        $reading->update(['added_to_invoice' => true, 'invoice_id' => $invoice->id]);
    }

    public function getPendingReadings(int $roomId): \Illuminate\Database\Eloquent\Collection
    {
        return UtilityReading::where('room_id', $roomId)
            ->where('added_to_invoice', false)
            ->orderByDesc('billing_period')
            ->get();
    }
}
