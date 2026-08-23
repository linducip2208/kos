<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * State machine status kamar.
 *
 * Transisi valid:
 *   available   → reserved | maintenance | blocked | cleaning | inactive
 *   reserved    → occupied (booking jadi) | available (batal)
 *   occupied    → notice_given | inspection | maintenance | available (checkout instan)
 *   notice_given→ occupied (batal notice) | inspection | available
 *   inspection  → cleaning | available | maintenance
 *   cleaning    → available | inspection
 *   maintenance → cleaning | inspection | available | blocked
 *   blocked     → available | maintenance | inactive
 *   inactive    → available | maintenance | blocked
 */
class RoomStatusService
{
    public const TRANSITIONS = [
        'available'    => ['reserved', 'maintenance', 'blocked', 'cleaning', 'inspection', 'inactive'],
        'reserved'     => ['occupied', 'available', 'blocked', 'inactive'],
        'occupied'     => ['notice_given', 'inspection', 'maintenance', 'available'],
        'notice_given' => ['occupied', 'inspection', 'available'],
        'inspection'   => ['cleaning', 'available', 'maintenance', 'occupied'],
        'cleaning'     => ['available', 'inspection', 'blocked'],
        'maintenance'  => ['cleaning', 'inspection', 'available', 'blocked'],
        'blocked'      => ['available', 'maintenance', 'inactive'],
        'inactive'     => ['available', 'maintenance', 'blocked'],
    ];

    public const COLORS = [
        'available'    => 'success',
        'reserved'     => 'info',
        'occupied'     => 'danger',
        'notice_given' => 'warning',
        'cleaning'     => 'gray',
        'inspection'   => 'purple',
        'maintenance'  => 'amber',
        'blocked'      => 'slate',
        'inactive'     => 'slate',
    ];

    public static function color(string $status): string
    {
        return self::COLORS[$status] ?? 'gray';
    }

    public function canTransition(Room $room, string $to): bool
    {
        if ($room->status === $to) {
            return true;
        }

        return in_array($to, self::TRANSITIONS[$room->status] ?? [], true);
    }

    /**
     * Terapkan transisi dengan validasi + side effect.
     */
    public function transition(Room $room, string $to, ?string $reason = null): void
    {
        $from = $room->status;

        if ($from === $to) {
            return;
        }

        if (!isset(Room::STATUSES[$to])) {
            throw ValidationException::withMessages([
                'status' => "Status kamar '{$to}' tidak dikenal.",
            ]);
        }

        if (!$this->canTransition($room, $to)) {
            throw ValidationException::withMessages([
                'status' => 'Transisi tidak valid: '.Room::statusLabel($from).' → '.Room::statusLabel($to).
                            '. Ikuti alur workflow kamar.',
            ]);
        }

        $room->status = $to;

        if ($to === 'blocked') {
            $room->blocked_reason = $reason;
        } elseif ($to === 'cleaning') {
            $room->last_cleaned_at = null;
        } elseif (in_array($to, ['available'], true)) {
            $room->last_cleaned_at = $room->last_cleaned_at ?? now();
            $room->blocked_reason  = null;
        }

        $room->save();

        // Sinkronkan lease aktif bila kamar keluar dari occupied secara paksa.
        if (in_array($from, ['occupied', 'notice_given'], true)
            && !in_array($to, ['occupied', 'notice_given'], true)) {
            $room->leases()->whereIn('status', ['active', 'expiring_soon'])
                ->update(['status' => 'ended']);
        }
    }
}
