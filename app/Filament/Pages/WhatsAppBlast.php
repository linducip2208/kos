<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesPageAccess;
use App\Support\NavigationGroups;
use Filament\Pages\Page;

class WhatsAppBlast extends Page
{
    use AuthorizesPageAccess;

    protected static ?string $permission = 'website.manage';

    protected string $view = 'filament.pages.whatsapp-blast';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::SETTINGS;
    }

    public static function getNavigationLabel(): string
    {
        return 'WhatsApp Blast';
    }

    public function getTitle(): string
    {
        return 'WhatsApp Blast';
    }

    public static function getNavigationBadge(): ?string
    {
        return 'Next Update';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
