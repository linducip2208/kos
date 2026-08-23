<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\UtilityRate;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OperationalAlert;

/**
 * Resolusi tarif utilitas berjenjang:
 * room → room_type → property → global (setting).
 * Termasuk kalkulasi biaya + deteksi pemakaian abnormal.
 */
class UtilityRateService
{
    /**
     * Cari tarif paling spesifik untuk sebuah kamar.
     */
    public function resolve(Room $room, string $utilityType): ?UtilityRate
    {
        return UtilityRate::query()
            ->where('is_active', true)
            ->where('utility_type', $utilityType)
            ->where(function ($q) use ($room) {
                $q->where(function ($qq) use ($room) {
                    $qq->where('scope', 'room')->where('room_id', $room->id);
                })->orWhere(function ($qq) use ($room) {
                    $qq->where('scope', 'room_type')->where('room_type_id', $room->room_type_id);
                })->orWhere(function ($qq) use ($room) {
                    $qq->where('scope', 'property')->where('property_id', $room->property_id);
                })->orWhere('scope', 'global');
            })
            ->orderByRaw("CASE scope
                WHEN 'room' THEN 1
                WHEN 'room_type' THEN 2
                WHEN 'property' THEN 3
                ELSE 4 END")
            ->first();
    }

    /**
     * Rate per unit final: utility_rates dulu, fallback setting global.
     */
    public function ratePerUnit(Room $room, string $utilityType): float
    {
        $rate = $this->resolve($room, $utilityType);

        if ($rate) {
            return (float) $rate->rate_per_unit;
        }

        return match ($utilityType) {
            'electricity' => (float) setting('electricity_rate', 1352, 'utility'),
            'water'       => (float) setting('water_rate', 6000, 'utility'),
            default       => (float) setting('gas_rate', 7000, 'utility'),
        };
    }

    /**
     * Kalkulasi total tagihan utilitas dari usage.
     *
     * total = max(usage, minimum_usage) × rate
     *       + fixed_charge + admin_charge
     *       (minimum_charge dipakai bila hasil < minimum_charge)
     */
    public function calculate(Room $room, string $utilityType, float $usage): array
    {
        $rate   = $this->resolve($room, $utilityType);
        $perUnit= $this->ratePerUnit($room, $utilityType);

        $billableUsage = $usage;
        $minApplied    = false;

        if ($rate && (float) $rate->minimum_usage > 0 && $usage < (float) $rate->minimum_usage) {
            $billableUsage = (float) $rate->minimum_usage;
            $minApplied    = true;
        }

        $usageAmount = $billableUsage * $perUnit;

        $fixed = (float) ($rate->fixed_charge ?? 0);
        $admin = (float) ($rate->admin_charge ?? 0);

        $total = $usageAmount + $fixed + $admin;

        if ($rate && (float) $rate->minimum_charge > 0 && $total < (float) $rate->minimum_charge) {
            $total = (float) $rate->minimum_charge;
        }

        return [
            'rate_per_unit' => $perUnit,
            'usage'         => $usage,
            'billable_usage'=> $billableUsage,
            'usage_amount'  => round($usageAmount, 2),
            'fixed_charge'  => $fixed,
            'admin_charge'  => $admin,
            'minimum_applied' => $minApplied,
            'total'         => round($total, 2),
            'breakdown'     => [
                ['label' => "Pemakaian {$billableUsage} unit × Rp ".number_format($perUnit, 2), 'amount' => round($usageAmount, 2)],
                ...( $fixed > 0 ? [['label' => 'Biaya tetap', 'amount' => $fixed]] : []),
                ...( $admin > 0 ? [['label' => 'Biaya admin', 'amount' => $admin]] : []),
            ],
        ];
    }

    /**
     * Deteksi pemakaian abnormal: > X kali rata-rata N periode sebelumnya.
     */
    public function isAbnormal(Room $room, string $utilityType, float $usage, int $times = 3): bool
    {
        if ($usage <= 0) {
            return false;
        }

        $avg = \App\Models\UtilityReading::where('room_id', $room->id)
            ->where('type', $utilityType)
            ->orderByDesc('billing_period')
            ->skip(1)               // skip periode terbaru
            ->take(3)
            ->get()
            ->avg(fn ($r) => (float) $r->current_reading - (float) $r->previous_reading);

        return $avg !== null && $avg > 0 && $usage >= ($times * $avg);
    }
}
