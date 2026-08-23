<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Room;
use App\Models\RoomTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Workflow kontrak sewa:
 * draft → pending_approval → awaiting_signature → active → (renewed|ended|terminated)
 *
 * Renewal SELALU membuat record lease baru yang ter-link ke lease sebelumnya.
 */
class LeaseWorkflowService
{
    public const TRANSITIONS = [
        'draft'              => ['pending_approval', 'active', 'cancelled'],
        'pending_approval'   => ['awaiting_signature', 'draft'],
        'awaiting_signature' => ['active', 'draft', 'pending_approval'],
        'active'             => ['ended', 'terminated', 'renewed', 'expiring_soon'],
        'expiring_soon'      => ['ended', 'terminated', 'renewed', 'active'],
        'renewed'            => [],
        'ended'              => [],
        'expired'            => [],
        'terminated'         => [],
    ];

    /**
     * Ajukan lease untuk approval.
     */
    public function submitForApproval(Lease $lease): Lease
    {
        $this->ensureTransition($lease, 'pending_approval');
        $lease->update(['status' => 'pending_approval']);

        return $lease;
    }

    /**
     * Setujui lease → masuk tahap menunggu tanda tangan tenant.
     */
    public function approve(Lease $lease, User $approver): Lease
    {
        if ($lease->status === 'draft') {
            $lease->status = 'pending_approval';
        }

        $this->ensureTransition($lease, 'awaiting_signature');

        $lease->update([
            'status'       => 'awaiting_signature',
            'approved_by'  => $approver->id,
            'approved_at'  => now(),
        ]);

        return $lease;
    }

    /**
     * Tenant tanda tangan → lease AKTIF, kamar jadi occupied.
     */
    public function activate(Lease $lease, bool $tenantSigned = true): Lease
    {
        if ($lease->status === 'draft') {
            // Aktivasi langsung dari draft dianggap sudah disetujui sistem
            $lease->approved_at = $lease->approved_at ?? now();
        }

        $this->ensureTransition($lease, 'active');

        $lease->forceFill([
            'status'           => 'active',
            'tenant_signed_at' => $tenantSigned ? ($lease->tenant_signed_at ?? now()) : $lease->tenant_signed_at,
            'owner_signed_at'  => $lease->owner_signed_at ?? now(),
        ])->save();

        app(RoomStatusService::class)->transition($lease->room, 'occupied');

        return $lease;
    }

    /**
     * Akhiri lease (selesai normal).
     */
    public function end(Lease $lease, ?string $reason = null): Lease
    {
        $this->ensureTransition($lease, 'ended');

        $lease->forceFill([
            'status'         => 'ended',
            'moved_out_at'   => today(),
            'termination_reason' => $reason ?? $lease->termination_reason,
        ])->save();

        return $lease;
    }

    /**
     * Terminasi dini.
     */
    public function terminate(Lease $lease, string $reason): Lease
    {
        $target = in_array('terminated', self::TRANSITIONS[$lease->status] ?? [], true)
            ? 'terminated'
            : 'ended';

        $lease->forceFill([
            'status'             => $target,
            'terminated_at'      => today(),
            'termination_reason' => $reason,
        ])->save();

        return $lease;
    }

    /**
     * Renewal: buat LEASE BARU ter-link ke lease lama.
     * Lease lama statusnya menjadi 'renewed'.
     *
     * @param array{start_date?:string,end_date?:string,price?:float,billing_cycle?:string} $overrides
     */
    public function renew(Lease $lease, array $overrides = [], ?User $by = null): Lease
    {
        $room = $lease->room;

        if (!$room) {
            throw ValidationException::withMessages(['room' => 'Lease tidak punya kamar.']);
        }

        if ($room->leases()->whereIn('status', ['active', 'expiring_soon'])->whereKeyNot($lease->getKey())->exists()) {
            throw ValidationException::withMessages(['room' => 'Kamar masih dipegang lease aktif lain.']);
        }

        $newStart = \Carbon\Carbon::parse($overrides['start_date'] ?? $lease->end_date->copy()->addDay());
        $newEnd   = \Carbon\Carbon::parse(
            $overrides['end_date'] ?? $newStart->copy()->addMonthsNoOverflow(12)->subDay()
        );

        DB::beginTransaction();
        try {
            $newLease = Lease::create([
                'room_id'               => $room->id,
                'occupant_id'           => $lease->occupant_id,
                'lease_number'          => $this->generateLeaseNumber(),
                'renewed_from_lease_id' => $lease->id,
                'start_date'            => $newStart->toDateString(),
                'end_date'              => $newEnd->toDateString(),
                'price'                 => $overrides['price'] ?? $lease->price,
                'deposit'               => $overrides['deposit'] ?? 0, // deposit tetap di ledger lama
                'billing_cycle'         => $overrides['billing_cycle'] ?? $lease->billing_cycle,
                'billing_date'          => $lease->billing_date,
                'status'                => 'awaiting_signature',
                'approved_by'           => $by?->id ?? auth()->id(),
                'approved_at'           => now(),
                'notes'                 => "Perpanjangan dari {$lease->lease_number}",
                'created_by'            => $by?->id ?? auth()->id(),
            ]);

            // Lease lama → renewed (riwayat utuh, tidak dioverwrite)
            $lease->forceFill(['status' => 'renewed'])->save();

            DB::commit();

            return $newLease;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Tandai tenant memberi notice akan keluar.
     */
    public function giveNotice(Lease $lease, ?\Carbon\Carbon $effectiveDate = null): Lease
    {
        $lease->forceFill([
            'notice_given_at' => today(),
            'end_date'        => $effectiveDate?->toDateString() ?? $lease->end_date,
        ])->save();

        try {
            app(RoomStatusService::class)->transition($lease->room, 'notice_given');
        } catch (ValidationException) {
            // kamar mungkin sudah bukan occupied
        }

        return $lease;
    }

    protected function ensureTransition(Lease $lease, string $to): void
    {
        $from = $lease->status;

        if ($from === $to) {
            return;
        }

        // alias legacy
        if ($from === 'expired') {
            $from = 'ended';
        }
        if ($from === 'pending') {
            $from = 'draft';
        }

        $allowed = self::TRANSITIONS[$from] ?? [];

        if (!in_array($to, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'Transisi kontrak tidak valid: '.Lease::statusLabel($from).' → '.Lease::statusLabel($to).'.',
            ]);
        }
    }

    public function generateLeaseNumber(): string
    {
        $prefix = config('koskosan.lease_prefix', 'LSE');

        do {
            $number = $prefix.'-'.now()->format('ym').'-'.strtoupper(\Illuminate\Support\Str::random(4));
        } while (Lease::where('lease_number', $number')->exists());

        return $number;
    }
}
