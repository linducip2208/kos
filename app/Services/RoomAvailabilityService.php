<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class RoomAvailabilityService
{
    public function assertAssignable(Room $room, ?Lease $ignoreLease = null): void
    {
        $hasAnotherLease = $room->leases()->whereIn('status', ['active', 'expiring_soon'])
            ->when($ignoreLease, fn ($q) => $q->whereKeyNot($ignoreLease->id))->exists();

        if ($hasAnotherLease || in_array($room->status, ['occupied', 'maintenance', 'blocked', 'inactive'], true)) {
            throw ValidationException::withMessages(['room' => 'Kamar tidak tersedia untuk dialokasikan.']);
        }
    }

    public function assertBookingAvailable(Room $room, Carbon $moveIn): void
    {
        if ($room->status !== 'available') {
            throw ValidationException::withMessages(['room_id' => 'Kamar sudah tidak tersedia. Silakan pilih kamar lain.']);
        }

        $hasReservation = $room->bookings()->whereIn('stage', ['reserved', 'deposit_pending', 'deposit_paid', 'contract'])
            ->whereDate('desired_move_in', $moveIn->toDateString())->exists();

        if ($hasReservation) {
            throw ValidationException::withMessages(['room_id' => 'Kamar baru saja dipesan oleh calon penyewa lain.']);
        }
    }
}
