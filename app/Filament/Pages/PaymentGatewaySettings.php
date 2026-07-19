<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentGatewaySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?int    $navigationSort  = 20;

    public static function getNavigationGroup(): ?string { return __('navigation.group_settings'); }
    public static function getNavigationLabel(): string  { return __('navigation.payment_gateway_settings'); }
    protected string  $view            = 'filament.pages.payment-gateway-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'payment_gateway_active'  => setting('payment_gateway_active', 'manual'),
            'midtrans_server_key'     => setting('midtrans_server_key', '', 'payment'),
            'midtrans_client_key'     => setting('midtrans_client_key', '', 'payment'),
            'midtrans_production'     => setting('midtrans_production', false, 'payment'),
            'tripay_api_key'          => setting('tripay_api_key', '', 'payment'),
            'tripay_private_key'      => setting('tripay_private_key', '', 'payment'),
            'tripay_merchant_code'    => setting('tripay_merchant_code', '', 'payment'),
            'tripay_production'       => setting('tripay_production', false, 'payment'),
            'tripay_default_channel'  => setting('tripay_default_channel', 'BRIVA', 'payment'),
            'manual_bank_info'        => setting('manual_bank_info', '', 'payment'),
            'invoice_penalty_percent' => setting('invoice_penalty_percent', 2),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Gateway Aktif')
                    ->description('Pilih metode pembayaran yang digunakan')
                    ->schema([
                        Select::make('payment_gateway_active')
                            ->label('Gateway Pembayaran')
                            ->options([
                                'manual'   => 'Manual (Transfer Bank / Tunai)',
                                'midtrans' => 'Midtrans (Snap)',
                                'tripay'   => 'Tripay',
                            ])
                            ->required()->live(),
                    ]),

                Section::make('Midtrans')
                    ->description('Konfigurasi Midtrans Snap')
                    ->visible(fn ($get) => $get('payment_gateway_active') === 'midtrans')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('midtrans_server_key')->label('Server Key')
                                ->password()->revealable()->placeholder('SB-Mid-server-xxxx'),
                            TextInput::make('midtrans_client_key')->label('Client Key')
                                ->placeholder('SB-Mid-client-xxxx'),
                        ]),
                        Toggle::make('midtrans_production')->label('Mode Production')
                            ->helperText('Matikan untuk menggunakan Sandbox'),
                    ]),

                Section::make('Tripay')
                    ->description('Konfigurasi Tripay')
                    ->visible(fn ($get) => $get('payment_gateway_active') === 'tripay')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('tripay_api_key')->label('API Key')->password()->revealable(),
                            TextInput::make('tripay_private_key')->label('Private Key')->password()->revealable(),
                            TextInput::make('tripay_merchant_code')->label('Merchant Code'),
                            Select::make('tripay_default_channel')->label('Channel Default')
                                ->options([
                                    'BRIVA' => 'BRI Virtual Account', 'BNIVA' => 'BNI Virtual Account',
                                    'BSIVA' => 'BSI Virtual Account', 'MANDIRIVA' => 'Mandiri Virtual Account',
                                    'BCAVA' => 'BCA Virtual Account', 'QRIS' => 'QRIS',
                                ]),
                        ]),
                        Toggle::make('tripay_production')->label('Mode Production')
                            ->helperText('Matikan untuk menggunakan Sandbox'),
                    ]),

                Section::make('Pembayaran Manual')
                    ->visible(fn ($get) => $get('payment_gateway_active') === 'manual')
                    ->schema([
                        TextInput::make('manual_bank_info')->label('Info Rekening Bank')
                            ->placeholder('BCA 1234567890 a/n Budi Santoso')
                            ->helperText('Ditampilkan ke penyewa saat bayar manual'),
                    ]),

                Section::make('Denda Keterlambatan')->schema([
                    TextInput::make('invoice_penalty_percent')
                        ->label('Persentase Denda (%)')
                        ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                        ->helperText('Denda dihitung per 30 hari keterlambatan. Set 0 untuk nonaktifkan.'),
                ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('payment_gateway_active', $data['payment_gateway_active']);
        Setting::set('invoice_penalty_percent', $data['invoice_penalty_percent'], 'general', 'integer');
        Setting::set('midtrans_server_key', $data['midtrans_server_key'], 'payment', 'encrypted');
        Setting::set('midtrans_client_key', $data['midtrans_client_key'], 'payment');
        Setting::set('midtrans_production', $data['midtrans_production'], 'payment', 'boolean');
        Setting::set('tripay_api_key', $data['tripay_api_key'], 'payment', 'encrypted');
        Setting::set('tripay_private_key', $data['tripay_private_key'], 'payment', 'encrypted');
        Setting::set('tripay_merchant_code', $data['tripay_merchant_code'], 'payment');
        Setting::set('tripay_default_channel', $data['tripay_default_channel'], 'payment');
        Setting::set('tripay_production', $data['tripay_production'], 'payment', 'boolean');
        Setting::set('manual_bank_info', $data['manual_bank_info'], 'payment');

        Notification::make()->title('Pengaturan payment gateway berhasil disimpan.')->success()->send();
    }
}
