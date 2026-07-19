<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Models\Lease;
use App\Services\PaymentGatewayService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model           = Invoice::class;
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-banknotes';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationGroup(): ?string { return '?? Keuangan'; }
    public static function getLabel(): ?string            { return __('navigation.invoice'); }
    public static function getPluralLabel(): ?string      { return __('navigation.invoices'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Info Tagihan')->schema([
                Grid::make(2)->schema([
                    Select::make('lease_id')
                        ->label('Kontrak')
                        ->options(
                            Lease::with(['occupant', 'room'])
                                ->where('status', 'active')
                                ->get()
                                ->mapWithKeys(fn ($l) => [$l->id => $l->occupant->name . ' - ' . $l->room->room_number . ' (' . $l->lease_number . ')'])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if (!$state) return;
                            $lease = Lease::find($state);
                            if ($lease) $set('base_amount', $lease->price);
                        }),
                    TextInput::make('invoice_number')->label('No. Invoice')->disabled()->dehydrated(),
                ]),
                Grid::make(3)->schema([
                    DatePicker::make('period_start')->label('Periode Mulai')->required(),
                    DatePicker::make('period_end')->label('Periode Akhir')->required(),
                    DatePicker::make('due_date')->label('Jatuh Tempo')->required(),
                ]),
            ]),

            Section::make('Rincian Biaya')->schema([
                TextInput::make('base_amount')->label('Harga Sewa')->numeric()->prefix('Rp')->required()
                    ->live(debounce: 500)->afterStateUpdated(fn (Get $get, Set $set) => static::recalcTotal($get, $set)),
                Repeater::make('additional_charges')
                    ->label('Biaya Tambahan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('label')->label('Keterangan')->required()->placeholder('Listrik, Air, dll'),
                            TextInput::make('amount')->label('Jumlah')->numeric()->prefix('Rp')->required(),
                        ]),
                    ])
                    ->collapsible()->live(debounce: 500)
                    ->afterStateUpdated(fn (Get $get, Set $set) => static::recalcTotal($get, $set))
                    ->defaultItems(0),
                Grid::make(2)->schema([
                    TextInput::make('discount')->label('Diskon')->numeric()->prefix('Rp')->default(0)
                        ->live(debounce: 500)->afterStateUpdated(fn (Get $get, Set $set) => static::recalcTotal($get, $set)),
                    TextInput::make('total')->label('Total')->numeric()->prefix('Rp')->disabled()->dehydrated(),
                ]),
                Textarea::make('notes')->label('Catatan')->rows(2),
            ]),

            Section::make('Status')->schema([
                Select::make('status')
                    ->options(['draft' => 'Draft', 'sent' => 'Terkirim', 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', 'cancelled' => 'Dibatalkan'])
                    ->default('draft')->required(),
            ]),
        ]);
    }

    private static function recalcTotal(Get $get, Set $set): void
    {
        $base       = (float) ($get('base_amount') ?? 0);
        $additional = collect($get('additional_charges') ?? [])->sum(fn ($i) => (float) ($i['amount'] ?? 0));
        $discount   = (float) ($get('discount') ?? 0);
        $set('total', $base + $additional - $discount);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->label('No. Invoice')->searchable()->weight('bold'),
                TextColumn::make('lease.occupant.name')->label('Penyewa')->searchable(),
                TextColumn::make('lease.room.room_number')->label('Kamar'),
                TextColumn::make('period_start')->label('Periode')->date('M Y'),
                TextColumn::make('due_date')->label('Jatuh Tempo')->date('d/m/Y')
                    ->color(fn (Invoice $r) => $r->is_overdue ? 'danger' : null),
                TextColumn::make('total')->label('Total')->money('IDR'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'draft' => 'gray', 'sent' => 'info', 'paid' => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'draft' => 'Draft', 'sent' => 'Terkirim', 'paid' => 'Lunas',
                        'overdue' => 'Tunggakan', 'cancelled' => 'Batal', default => $s,
                    }),
            ])
            ->filters([
                SelectFilter::make('lease_id')->label('Kontrak')
                    ->options(fn () => \App\Models\Lease::with('occupant')
                        ->get()->pluck('lease_number', 'id'))->searchable(),
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'sent' => 'Terkirim', 'paid' => 'Lunas',
                    'overdue' => 'Tunggakan', 'cancelled' => 'Batal',
                ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('print_invoice')
                    ->label('Cetak Tagihan')->icon('heroicon-o-printer')->color('gray')
                    ->url(fn (Invoice $r) => route('print.invoice', $r))
                    ->openUrlInNewTab(),
                Actions\Action::make('print_kwitansi')
                    ->label('Cetak Kwitansi')->icon('heroicon-o-document-check')->color('success')
                    ->visible(fn (Invoice $r) => $r->status === 'paid')
                    ->url(fn (Invoice $r) => route('print.kwitansi', $r))
                    ->openUrlInNewTab(),
                Actions\Action::make('pay_online')
                    ->label('Bayar Online')->icon('heroicon-o-credit-card')->color('success')
                    ->visible(fn (Invoice $r) => in_array($r->status, ['sent', 'overdue']) && setting('payment_gateway_active', 'manual') !== 'manual')
                    ->action(function (Invoice $record) {
                        $result = app(PaymentGatewayService::class)->createTransaction($record);
                        if ($result['success']) {
                            Notification::make()->title('Link pembayaran dibuat.')->success()->send();
                        } else {
                            Notification::make()->title('Gagal: ' . ($result['message'] ?? 'Error'))->danger()->send();
                        }
                    }),
                Actions\Action::make('mark_paid')
                    ->label('Tandai Lunas')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Invoice $r) => in_array($r->status, ['sent', 'overdue']))
                    ->requiresConfirmation()
                    ->action(fn (Invoice $r) => $r->update(['status' => 'paid', 'paid_at' => now(), 'payment_channel' => 'manual'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
