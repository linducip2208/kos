<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Models\Lease;
use App\Support\NavigationGroups;
use Filament\Pages\Page;

class LeaseRenewalReport extends Page
{
    use AuthorizesPageAccess;

    protected static ?string $permission = 'report.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::REPORTS;
    }

    public static function getNavigationLabel(): string
    {
        return 'Perpanjangan Kontrak';
    }

    public function getTitle(): string
    {
        return 'Laporan Perpanjangan Kontrak';
    }

    protected string $view = 'filament.pages.lease-renewal-report';

    public function getViewData(): array
    {
        $propertyIds = auth()->user()?->scopedPropertyIds();
        $scope = fn ($q) => $propertyIds === null ? $q : $q->whereHas('room',
            fn ($r) => $r->whereIn('property_id', $propertyIds ?: [0]));

        $expiring30 = Lease::with(['occupant', 'room.property'])
            ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->whereIn('status', ['active', 'expiring_soon'])
            ->tap($scope)
            ->orderBy('end_date')
            ->get();

        $expiring7 = $expiring30->filter(fn ($l) => $l->end_date->lte(now()->addDays(7)->endOfDay()));
        $expired = Lease::where('end_date', '<', now()->startOfDay())
            ->whereIn('status', ['active', 'expiring_soon'])
            ->tap($scope)
            ->with(['occupant', 'room.property'])->orderBy('end_date')->get();

        return compact('expiring30', 'expiring7', 'expired');
    }
}
