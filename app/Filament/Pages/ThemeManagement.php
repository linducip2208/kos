<?php

namespace App\Filament\Pages;

use App\Core\Theme\ThemeManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ThemeManagement extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?int    $navigationSort = 21;
    protected string $view = 'filament.pages.theme-management';

    public static function getNavigationGroup(): ?string { return '?? Sistem'; }
    public static function getNavigationLabel(): string  { return 'Theme Management'; }

    public function getThemes(): array
    {
        $manager = app(ThemeManager::class);
        return [
            'admin'    => $manager->getAll('admin'),
            'user'     => $manager->getAll('user'),
            'frontend' => $manager->getAll('frontend'),
        ];
    }

    public function activate(string $area, string $slug): void
    {
        $manager = app(ThemeManager::class);
        $result  = $manager->activate($area, $slug);

        if ($result['success']) {
            Notification::make()->title($result['message'])->success()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }
}
