<?php

namespace App\Filament\Pages;

use App\Core\License\LicenseService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LicenseSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';
    protected static ?int    $navigationSort = 30;
    protected string $view = 'filament.pages.license-settings';

    public static function getNavigationGroup(): ?string { return __('navigation.group_settings'); }
    public static function getNavigationLabel(): string  { return 'Lisensi'; }

    public string $activationKey = '';

    public function getLicenseInfo(): array
    {
        return app(LicenseService::class)->info();
    }

    public function activate(): void
    {
        $key = trim($this->activationKey);

        if (!$key) {
            Notification::make()->title('Masukkan activation key terlebih dahulu.')->warning()->send();
            return;
        }

        $result = app(LicenseService::class)->activate($key);

        if ($result['success']) {
            $this->activationKey = '';
            Notification::make()->title($result['message'])->success()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }

    public function revalidate(): void
    {
        \Illuminate\Support\Facades\Cache::forget('license_valid');
        $result = app(LicenseService::class)->validate();

        if ($result['valid'] ?? false) {
            Notification::make()->title($result['message'])->success()->send();
        } else {
            Notification::make()->title($result['message'])->danger()->send();
        }
    }

    public function checkUpdate(): void
    {
        $result = app(LicenseService::class)->checkUpdate();

        if (!($result['success'] ?? false)) {
            Notification::make()->title('Tidak dapat memeriksa pembaruan.')->warning()->send();
            return;
        }

        if ($result['has_update'] ?? false) {
            Notification::make()
                ->title("Versi baru tersedia: {$result['latest_version']}")
                ->body('Unduh di panel admin whitelabel.co.id.')
                ->success()
                ->send();
        } else {
            Notification::make()->title('Aplikasi sudah versi terbaru.')->success()->send();
        }
    }

    public function revoke(): void
    {
        $info = $this->getLicenseInfo();
        $key  = $info['key'];

        if (!$key || $key === 'Belum diaktifkan') {
            Notification::make()->title('Tidak ada lisensi aktif untuk di-revoke.')->warning()->send();
            return;
        }

        $result = app(LicenseService::class)->revoke($key);
        Notification::make()->title($result['message'])->warning()->send();
    }
}
