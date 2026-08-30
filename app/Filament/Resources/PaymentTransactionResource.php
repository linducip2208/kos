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
                    TextColumn::make('invoice.number')->label('Invoice'),
                    TextColumn::make('amount')->label('Jumlah')->money('IDR'),
                    TextColumn::make('payment_method')->label('Metode'),
                    TextColumn::make('status')->label('Status')->badge(),
                    TextColumn::make('paid_at')->label('Tanggal Bayar')->dateTime('d M Y H:i'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width(60),
                TextColumn::make('invoice.number')->label('Invoice')->searchable(),
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('payment_method')->label('Metode')->badge()->color('gray'),
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
                SelectFilter::make('payment_method')->options([
                    'cash' => 'Tunai', 'transfer' => 'Transfer', 'midtrans' => 'Midtrans', 'tripay' => 'Tripay',
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
