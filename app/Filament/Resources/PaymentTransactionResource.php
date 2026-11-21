<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\PaymentTransaction;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = PaymentTransaction::class;

    protected static array $tablePreloads = [
        'invoice',
    ];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::FINANCE;
    }

    public static function getLabel(): ?string
    {
        return 'Pembayaran';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Pembayaran';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Pembayaran')->schema([
                Grid::make(2)->schema([
                    \Filament\Forms\Components\Placeholder::make('invoice_number')->label('Invoice')
                        ->content(fn ($record) => $record?->invoice?->invoice_number ?? '-'),
                    \Filament\Forms\Components\Placeholder::make('amount')->label('Jumlah')
                        ->content(fn ($record) => $record ? 'Rp '.number_format((float) $record->amount, 0, ',', '.') : '-'),
                    \Filament\Forms\Components\Placeholder::make('payment_type')->label('Tipe Pembayaran')
                        ->content(fn ($record) => $record?->payment_type ?? '-'),
                    \Filament\Forms\Components\Placeholder::make('status')->label('Status')
                        ->content(fn ($record) => $record?->status ?? '-'),
                    \Filament\Forms\Components\Placeholder::make('paid_at')->label('Tanggal Bayar')
                        ->content(fn ($record) => $record?->paid_at?->format('d M Y H:i') ?? '-'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width(60),
                TextColumn::make('invoice.invoice_number')->label('Invoice')->searchable(),
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('payment_type')->label('Tipe')->badge()->color('gray')
                    ->formatStateUsing(fn ($s) => $s ?? '-'),
                TextColumn::make('channel')->label('Channel')->placeholder('-'),
                TextColumn::make('gateway')->label('Gateway')->placeholder('-'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $s): string => match ($s) {
                        'success' => 'success', 'pending' => 'warning', 'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('paid_at')->label('Dibayar')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'success' => 'Sukses', 'pending' => 'Pending', 'failed' => 'Gagal',
                ]),
                SelectFilter::make('payment_type')->label('Tipe')->options([
                    'qris' => 'QRIS', 'va' => 'Virtual Account', 'ewallet' => 'E-Wallet',
                    'bank_transfer' => 'Transfer Bank', 'cash' => 'Tunai',
                ]),
                SelectFilter::make('gateway')->label('Gateway')->options([
                    'midtrans' => 'Midtrans', 'tripay' => 'Tripay', 'manual' => 'Manual',
                ]),
            ])
            ->actions([Actions\ViewAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
            'view' => Pages\ViewPaymentTransaction::route('/{record}'),
        ];
    }
}
