<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Models\RoomTransfer;
use Illuminate\Support\Facades\DB;

/**
 * Pindah kamar: lease dipertahankan (riwayat utuh), kamar asal masuk
 * tahap cleaning, kamar tujuan jadi occupied, selisih harga di-prorate.
 */
class MoveRoomService
{
    /**
     * Hitung prorate: selisih harga × sisa hari / total hari periode berjalan.
     * Positif = tenant menambah, negatif = kredit tenant.
     */
    public function calculateProrate(Lease $lease, Room $toRoom): float
    {
        $fromPrice = $lease->price;
        $toPrice   = $toRoom->effective_price_monthly ?: $toRoom->roomType?->base_price_monthly ?? 0;

        if ((float) $toPrice === (float) $fromPrice) {
            return 0.0;
        }

        // Basis prorate: sisa hari sampai akhir bulan berjalan (atau akhir kontrak bila lebih pendek)
        $today     = today();
        $monthEnd  = $today->copy()->endOfMonth();
        $reference = min($monthEnd, $lease->end_date ?? $monthEnd);

        $remainingDays = max(0, (int) $today->diffInDays($reference) + 1);
        $daysInMonth   = (int) $today->daysInMonth;

        $dailyDiff = ((float) $toPrice - (float) $fromPrice) / $daysInMonth;

        return round($dailyDiff * $remainingDays, 2);
    }

    /**
     * Eksekusi pindah kamar.
     *
     * @param array{effective_date?:string|object,prorate_amount?:float|null,
     *              transfer_deposit?:bool,inspection_required?:bool,notes?:string} $options
     */
    public function transfer(Lease $lease, Room $toRoom, array $options = []): RoomTransfer
    {
        $fromRoom = $lease->room;

        if (!$fromRoom) {
            abort(422, 'Kontrak tidak memiliki kamar.');
        }

        if ($fromRoom->id === $toRoom->id) {
            abort(422, 'Kamar tujuan sama dengan kamar asal.');
        }

        if (!$toRoom->is_active || in_array($toRoom->status, ['occupied', 'reserved', 'inactive'], true)) {
            abort(422, 'Kamar tujuan tidak tersedia (status: '.$toRoom->status_label.').');
        }

        $effectiveDate = isset($options['effective_date'])
            ? \Carbon\Carbon::parse($options['effective_date'])
            : today();

        $prorate = $options['prorate_amount'] ?? $this->calculateProrate($lease, $toRoom);

        return DB::transaction(function () use ($lease, $fromRoom, $toRoom, $effectiveDate, $prorate, $options) {
            // 1. Riwayat transfer
            $transfer = RoomTransfer::create([
                'lease_id'           => $lease->id,
                'from_room_id'       => $fromRoom->id,
                'to_room_id'         => $toRoom->id,
                'effective_date'     => $effectiveDate->toDateString(),
                'prorate_amount'     => $prorate,
                'transfer_deposit'   => $options['transfer_deposit'] ?? true,
                'final_utility_done' => false,
                'inspection_required'=> $options['inspection_required'] ?? true,
                'status'             => 'completed',
                'notes'              => $options['notes'] ?? null,
                'performed_by'       => auth()->id(),
            ]);

            // 2. Update lease → kamar baru (riwayat tetap tersimpan di room_transfers)
            $lease->forceFill(['room_id' => $toRoom->id])->saveQuietly();

            // 3. Invoice prorate bila ada selisih
            if (abs((float) $prorate) > 0.009) {
                Invoice::create([
                    'lease_id'      => $lease->id,
                    'invoice_number'=> $this->generateAdjustmentNumber(),
                    'period_start'  => $effectiveDate->copy()->startOfMonth(),
                    'period_end'    => $effectiveDate->copy()->endOfMonth(),
                    'due_date'      => now()->addDays(7),
                    'base_amount'   => abs((float) $prorate),
                    'total'         => abs((float) $prorate),
                    'additional_charges' => [[
                        'label'  => ($prorate > 0 ? 'Selisih harga pindah kamar '
                                    : 'Kredit pindah kamar ').$fromRoom->room_number.' → '.$toRoom->room_number,
                        'amount' => abs((float) $prorate),
                    ]],
                    'status' => 'sent',
                    'sent_at'=> now(),
                    'created_by' => auth()->id(),
                    'notes'  => 'Auto-generated oleh Move Room',
                ]);
            }

            // 4. Status kamar: asal → cleaning; tujuan → occupied
            try {
                app(RoomStatusService::class)->transition($fromRoom, 'cleaning', 'Tenant pindah ke '.$toRoom->room_number);
            } catch (\Throwable) {
                $fromRoom->forceFill(['status' => 'cleaning'])->saveQuietly();
            }

            $toRoom->forceFill(['status' => 'occupied'])->saveQuietly();

            return $transfer;
        });
    }

    protected function generateAdjustmentNumber(): string
    {
        do {
            $number = 'ADJ-'.now()->format('ym').'-'.strtoupper(\Illuminate\Support\Str::random(4));
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}
