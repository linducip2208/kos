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
        $expiring30 = Lease::with(['occupant', 'room.property'])
            ->where('end_date', '<=', now()->addDays(30))
            ->where('end_date', '>=', now())
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get();

        $expiring7 = $expiring30->filter(fn ($l) => $l->end_date->lte(now()->addDays(7)));
        $expired = Lease::where('end_date', '<', now())->where('status', 'active')->with(['occupant', 'room.property'])->orderBy('end_date')->get();

        return compact('expiring30', 'expiring7', 'expired');
    }
}
