<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class WhatsAppBlast extends Page
{
    protected string $view = 'filament.pages.whatsapp-blast';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string { return '🔌 Integrasi'; }
    public static function getNavigationLabel(): string  { return 'WhatsApp Blast'; }
    public function getTitle(): string { return 'WhatsApp Blast'; }

    public static function getNavigationBadge(): ?string      { return 'Next Update'; }
    public static function getNavigationBadgeColor(): ?string { return 'warning'; }
}
