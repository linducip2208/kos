<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\User;
use App\Notifications\LeaseExpiringNotification;
use Illuminate\Console\Command;

class LeaseCheckExpiringCommand extends Command
{
    protected $signature = 'lease:check-expiring {--days=30 : Hari ke depan untuk dicek}';

    protected $description = 'Tampilkan kontrak yang akan berakhir dalam N hari';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $leases = Lease::whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->with(['occupant', 'room.property'])
            ->orderBy('end_date')
            ->get();

        if ($leases->isEmpty()) {
            $this->info("Tidak ada kontrak yang berakhir dalam {$days} hari ke depan.");

            return Command::SUCCESS;
        }

        $this->info("Kontrak yang berakhir dalam {$days} hari:");

        $rows = $leases->map(fn ($l) => [
            $l->lease_number,
            $l->occupant->name,
            $l->room->property->name.' - '.$l->room->room_number,
            $l->end_date->format('d/m/Y'),
            $l->end_date->diffForHumans(),
        ]);

        $this->table(['No. Kontrak', 'Penyewa', 'Kamar', 'Berakhir', 'Sisa'], $rows->toArray());

        // Kirim in-app notification ke semua admin/owner (dedupe per lease).
        $admins = User::whereIn('role', ['owner', 'super_admin', 'property_manager'])->where('is_active', true)->get();
        foreach ($leases as $lease) {
            $daysLeft = (int) now()->diffInDays($lease->end_date, false);
            foreach ($admins as $admin) {
                $already = $admin->notifications()
                    ->whereNull('read_at')
                    ->where('data->lease_id', $lease->id)
                    ->exists();
                if (! $already) {
                    $admin->notify(new LeaseExpiringNotification($lease, $daysLeft));
                }
            }
        }

        return Command::SUCCESS;
    }
}
