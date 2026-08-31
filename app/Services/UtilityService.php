<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\UtilityReading;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UtilityService
{
    public function getDefaultRate(string $type): float
    {
        return match ($type) {
            'electricity' => (float) setting('electricity_rate', 1352, 'utility'),
            'water' => (float) setting('water_rate', 6000, 'utility'),
            'gas' => (float) setting('gas_rate', 7000, 'utility'),
            default => 0,
        };
    }

    public function recordReading(
        int $roomId,
        string $type,
        float $currentReading,
        string $billingPeriod,
        ?float $rateOverride = null
    ): UtilityReading {
        if (! in_array($type, ['electricity', 'water', 'gas'], true)) {
            throw ValidationException::withMessages(['type' => 'Jenis utilitas tidak valid.']);
        }
        if ($currentReading < 0) {
            throw ValidationException::withMessages(['current_reading' => 'Bacaan meter tidak boleh negatif.']);
        }

        return DB::transaction(function () use ($roomId, $type, $currentReading, $billingPeriod, $rateOverride) {
            $previous = UtilityReading::where('room_id', $roomId)
                ->where('type', $type)
                ->where('billing_period', '<', $billingPeriod)
                ->orderByDesc('billing_period')
                ->lockForUpdate()
                ->value('current_reading') ?? 0;

            if ($currentReading < (float) $previous) {
                throw ValidationException::withMessages(['current_reading' => 'Bacaan sekarang tidak boleh lebih kecil dari bacaan sebelumnya.']);
            }

            $rate = $rateOverride ?? $this->getDefaultRate($type);
            $lease = Lease::where('room_id', $roomId)->whereIn('status', ['active', 'expiring_soon'])->first();

            return UtilityReading::updateOrCreate(
                ['room_id' => $roomId, 'type' => $type, 'billing_period' => $billingPeriod],
                [
                    'lease_id' => $lease?->id,
                    'previous_reading' => $previous,
                    'current_reading' => $currentReading,
                    'rate_per_unit' => $rate,
                    'amount' => round(($currentReading - $previous) * $rate, 2),
                    'reading_date' => now()->toDateString(),
                ]
            );
        });
    }

    public function addToInvoice(UtilityReading $reading, Invoice $invoice): void
    {
        if ($reading->added_to_invoice || $reading->invoice_id) {
            throw ValidationException::withMessages(['reading' => 'Pembacaan ini sudah masuk invoice.']);
        }
        if (! in_array($invoice->status, ['draft', 'sent'], true)) {
            throw ValidationException::withMessages(['invoice' => 'Invoice tidak dapat diubah pada status ini.']);
        }
        $charges = $invoice->additional_charges ?? [];
        $label = match ($reading->type) {
            'electricity' => 'Listrik',
            'water' => 'Air',
            'gas' => 'Gas',
            default => ucfirst($reading->type),
        };

        $charges[] = ['label' => "{$label} ({$reading->usage} unit)", 'amount' => $reading->amount];

        $newTotal = $invoice->base_amount
            + collect($charges)->sum('amount')
            - $invoice->discount;

        $invoice->update([
            'additional_charges' => $charges,
            'total' => $newTotal,
        ]);

        $reading->update(['added_to_invoice' => true, 'invoice_id' => $invoice->id]);
    }

    public function getPendingReadings(int $roomId): Collection
    {
        return UtilityReading::where('room_id', $roomId)
            ->where('added_to_invoice', false)
            ->orderByDesc('billing_period')
            ->get();
    }
}
