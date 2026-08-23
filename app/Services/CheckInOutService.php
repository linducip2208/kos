<?php

namespace App\Services;

use App\Models\CheckinRecord;
use App\Models\Lease;
use App\Models\Room;
use App\Models\RoomInventoryItem;
use Illuminate\Support\Facades\DB;

/**
 * Proses check-in & check-out profesional.
 *
 * Check-in : verifikasi identitas + lease + deposit, meter awal,
 *            checklist inventaris, foto, serah kunci, acknowledgement.
 * Check-out: meter akhir, inspeksi, item hilang/rusak, biaya cleaning,
 *            settlement deposit, room → cleaning/inspection.
 */
class CheckInOutService
{
    public function __construct(
        protected DepositService $deposits,
        protected UtilityService $utility,
    ) {}

    /**
     * Checklist otomatis dari inventaris kamar.
     */
    public function buildChecklist(Room $room): array
    {
        return $room->inventoryItems()
            ->orderBy('category')
            ->get()
            ->map(fn (RoomInventoryItem $item) => [
                'inventory_item_id' => $item->id,
                'name'              => $item->name,
                'quantity'          => $item->quantity,
                'expected_condition'=> $item->condition,
                'replacement_value' => (float) $item->replacement_value,
                'condition'         => null,   // diisi saat checklist
                'notes'             => null,
            ])
            ->all();
    }

    /**
     * Lakukan check-in untuk lease.
     *
     * @param array{meter_electric?:float,meter_water?:float,checklist?:array,photos?:array,
     *              key_handover?:bool,acknowledged_by?:string} $data
     */
    public function checkIn(Lease $lease, array $data = []): CheckinRecord
    {
        if (!$lease->isOperational()) {
            abort(422, 'Kontrak belum aktif — aktifkan kontrak sebelum check-in.');
        }

        if ($lease->checkinRecords()->where('type', 'check_in')->exists()) {
            abort(422, 'Tenant sudah pernah check-in pada kontrak ini.');
        }

        $room = $lease->room;

        return DB::transaction(function () use ($lease, $room, $data) {
            $record = CheckinRecord::create([
                'lease_id'       => $lease->id,
                'room_id'        => $room->id,
                'occupant_id'    => $lease->occupant_id,
                'type'           => 'check_in',
                'meter_electric_prev' => null,
                'meter_electric_current' => $data['meter_electric'] ?? null,
                'meter_water_prev' => null,
                'meter_water_current' => $data['meter_water'] ?? null,
                'checklist'      => $data['checklist'] ?? $this->buildChecklist($room),
                'photos'         => $data['photos'] ?? [],
                'key_handover'   => $data['key_handover'] ?? true,
                'acknowledged_by'=> $data['acknowledged_by'] ?? $lease->occupant?->name,
                'acknowledged_at'=> now(),
                'performed_by'   => auth()->id(),
                'completed_at'   => now(),
            ]);

            app(RoomStatusService::class)->transition($room, 'occupied');

            return $record;
        });
    }

    /**
     * Lakukan check-out: hitung kerusakan/missing dari checklist,
     * catat settlement, potong deposit, ubah status kamar.
     *
     * @param array{meter_electric?:float,meter_water?:float,checklist?:array,photos?:array,
     *              missing_items?:array,damage_amount?:float,cleaning_amount?:float,
     *              key_returned?:bool,execute_settlement?:bool,acknowledged_by?:string} $data
     */
    public function checkOut(Lease $lease, array $data = []): CheckinRecord
    {
        $checkin = $lease->checkinRecords()->where('type', 'check_in')->first();

        $room = $lease->room;

        $missingItems = collect($data['missing_items'] ?? [])
            ->map(fn ($m) => [
                'name'              => $m['name'] ?? '-',
                'replacement_value' => (float) ($m['replacement_value'] ?? 0),
            ])->values()->all();

        $missingTotal = collect($missingItems)->sum('replacement_value');
        $damageAmount = round((float) ($data['damage_amount'] ?? 0), 2);
        $cleaningAmount = round((float) ($data['cleaning_amount'] ?? 0), 2);

        // Utilitas belum ditagihkan
        $unpaidUtility = (float) ($lease
            ? $lease->utilityReadings()->where('added_to_invoice', false)->sum('amount')
            : 0);

        $record = DB::transaction(function () use ($lease, $room, $data, $missingItems, $missingTotal, $damageAmount, $cleaningAmount, $unpaidUtility) {
            $rec = CheckinRecord::create([
                'lease_id'       => $lease->id,
                'room_id'        => $room->id,
                'occupant_id'    => $lease->occupant_id,
                'type'           => 'check_out',
                'meter_electric_prev' => $this->lastMeter($lease, 'electric'),
                'meter_electric_current' => $data['meter_electric'] ?? null,
                'meter_water_prev' => $this->lastMeter($lease, 'water'),
                'meter_water_current' => $data['meter_water'] ?? null,
                'checklist'      => $data['checklist'] ?? $this->buildChecklist($room),
                'photos'         => $data['photos'] ?? [],
                'missing_items'  => $missingItems,
                'key_handover'   => $data['key_returned'] ?? true,
                'damage_amount'  => $damageAmount + $missingTotal,
                'cleaning_amount'=> $cleaningAmount,
                'unpaid_utility' => $unpaidUtility,
                'acknowledged_by'=> $data['acknowledged_by'] ?? $lease->occupant?->name,
                'acknowledged_at'=> now(),
                'performed_by'   => auth()->id(),
                'completed_at'   => now(),
            ]);

            app(LeaseWorkflowService::class)->end($lease, 'Check-out normal');

            return $rec;
        });

        // Settlement deposit bila diminta dan ada deposit
        if ($data['execute_settlement'] ?? true) {
            $deposit = $lease->deposits()->whereNotIn('status', ['refunded', 'forfeited'])->latest('id')->first();
            if ($deposit) {
                $settlement = $this->deposits->buildCheckoutSettlement($deposit);
                $record->forceFill([
                    'settlement'      => $settlement,
                    'tenant_payable'  => $settlement['tenant_payable'],
                    'deposit_deduction' => $settlement['deduction'],
                ])->saveQuietly();
                $this->deposits->executeCheckoutSettlement($deposit, $settlement);
            }
        }

        // Kamar masuk tahap cleaning setelah checkout selesai
        try {
            app(RoomStatusService::class)->transition($room, 'cleaning');
        } catch (\Throwable) {
            try {
                app(RoomStatusService::class)->transition($room, 'inspection');
            } catch (\Throwable) {
                // kamar mungkin sudah dipindahkan
            }
        }

        return $record;
    }

    protected function lastMeter(Lease $lease, string $kind): ?float
    {
        $col = "meter_{$kind}_current";

        return CheckinRecord::where('lease_id', $lease->id)
            ->whereNotNull($col)
            ->orderByDesc('id')
            ->value($col);
    }
}
