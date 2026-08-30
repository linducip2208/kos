<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Property;
use App\Models\Room;
use App\Support\NavigationGroups;
use Filament\Pages\Page;

class OccupancyReport extends Page
{
    use AuthorizesPageAccess;

    protected static ?string $permission = 'report.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::REPORTS;
    }

    public static function getNavigationLabel(): string
    {
        return 'Okupansi Kamar';
    }

    protected string $view = 'filament.pages.occupancy-report';

    public function getViewData(): array
    {
        $totalRooms = Room::where('is_active', true)->count();
        $occupied = Room::where('status', 'occupied')->count();
        $available = Room::where('status', 'available')->count();
        $maintenance = Room::where('status', 'maintenance')->count();

        $properties = Property::withCount(['rooms', 'rooms as occupied_rooms' => fn ($q) => $q->where('status', 'occupied')])
            ->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'total' => $p->rooms_count,
                'occupied' => $p->occupied_rooms,
                'rate' => $p->rooms_count > 0 ? round(($p->occupied_rooms / $p->rooms_count) * 100) : 0,
            ]);

        return compact('totalRooms', 'occupied', 'available', 'maintenance', 'properties');
    }
}
