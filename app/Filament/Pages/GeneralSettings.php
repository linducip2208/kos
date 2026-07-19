<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationGroup(): ?string { return '?? Sistem'; }
    public static function getNavigationLabel(): string  { return __('navigation.general_settings'); }
    protected string  $view            = 'filament.pages.general-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'app_name'          => setting('app_name', 'Kos Manager'),
            'app_logo'          => setting('app_logo', ''),
            'invoice_prefix'    => setting('invoice_prefix', 'INV'),
            'lease_prefix'      => setting('lease_prefix', 'KTR'),
            'reminder_days'     => setting('reminder_days', 3),
            // Kontak untuk landing page & footer
            'contact_whatsapp'  => setting('contact_whatsapp', ''),
            'contact_phone'     => setting('contact_phone', ''),
            'contact_email'     => setting('contact_email', ''),
            'contact_address'   => setting('contact_address', ''),
            // Notifikasi WA
            'whatsapp_enabled'  => setting('whatsapp_enabled', false, 'notif'),
            'whatsapp_api_key'  => setting('whatsapp_api_key', '', 'notif'),
            'whatsapp_sender'   => setting('whatsapp_sender', '', 'notif'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Aplikasi')->schema([
                Grid::make(2)->schema([
                    TextInput::make('app_name')->label('Nama Aplikasi')->required(),
                    FileUpload::make('app_logo')->label('Logo Aplikasi')->image()->directory('app')->imagePreviewHeight('60'),
                    TextInput::make('invoice_prefix')->label('Prefix Invoice')->maxLength(10),
                    TextInput::make('lease_prefix')->label('Prefix Nomor Kontrak')->maxLength(10),
                    TextInput::make('reminder_days')->label('Reminder Tagihan (hari sebelum jatuh tempo)')
                        ->numeric()->minValue(1)->maxValue(30),
                ]),
            ]),

            Section::make('Kontak & Landing Page')
                ->description('Ditampilkan di footer dan tombol WhatsApp landing page')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('contact_whatsapp')
                            ->label('Nomor WhatsApp Kontak')
                            ->placeholder('628xxxxxxxxxx')
                            ->helperText('Format internasional tanpa +, contoh: 6281234567890'),
                        TextInput::make('contact_phone')
                            ->label('Nomor Telepon')
                            ->placeholder('021-xxxxxxx'),
                        TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->placeholder('info@kos.com'),
                    ]),
                    Textarea::make('contact_address')->label('Alamat Kantor / Kantor Pengelola')->rows(2)->columnSpanFull(),
                ]),

            Section::make('Notifikasi WhatsApp (Fonnte)')->schema([
                Toggle::make('whatsapp_enabled')->label('Aktifkan WhatsApp Notif')->live(),
                Grid::make(2)->schema([
                    TextInput::make('whatsapp_api_key')->label('API Key Fonnte')
                        ->password()->revealable()->visible(fn ($get) => $get('whatsapp_enabled')),
                    TextInput::make('whatsapp_sender')->label('Nomor WhatsApp Pengirim')
                        ->placeholder('628xxxxxxxxxx')->visible(fn ($get) => $get('whatsapp_enabled')),
                ]),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('app_name', $data['app_name']);
        Setting::set('app_logo', $data['app_logo'] ?? '');
        Setting::set('invoice_prefix', $data['invoice_prefix']);
        Setting::set('lease_prefix', $data['lease_prefix']);
        Setting::set('reminder_days', $data['reminder_days'], 'general', 'integer');
        Setting::set('contact_whatsapp', $data['contact_whatsapp'] ?? '');
        Setting::set('contact_phone', $data['contact_phone'] ?? '');
        Setting::set('contact_email', $data['contact_email'] ?? '');
        Setting::set('contact_address', $data['contact_address'] ?? '');
        Setting::set('whatsapp_enabled', $data['whatsapp_enabled'], 'notif', 'boolean');
        Setting::set('whatsapp_api_key', $data['whatsapp_api_key'], 'notif', 'encrypted');
        Setting::set('whatsapp_sender', $data['whatsapp_sender'], 'notif');

        Notification::make()->title('Pengaturan disimpan.')->success()->send();
    }
}
