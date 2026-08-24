<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositTransactionResource\Pages;
use App\Models\Deposit;
use App\Models\DepositTransaction;
use App\Models\InvoicePayment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepositTransactionResource extends Resource
{
    protected static ?string $model = DepositTransaction::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 26;

    public static function getNavigationGroup(): ?string { return '💰 Keuangan'; }
    public static function getLabel(): ?string { return 'Mutasi Deposit'; }
    public static function getPluralLabel(): ?string { return 'Ledger Deposit'; }

    public static function canCreate(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('deposit.tenant.name')->label('Penyewa')->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($s) => match ($s) {
                        'receipt' => 'success', 'deduction' => 'danger',
                        'refund' => 'info', 'forfeit' => 'warning', default => 'gray',
                    })
                    ->formatStateUsing(fn ($s) => DepositTransaction::TYPES[$s] ?? $s),
                TextColumn::make('amount')->label('Mutasi')->weight('bold')
                    ->color(fn (DepositTransaction $r) => $r->signed_amount >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (DepositTransaction $r) =>
                        ($r->signed_amount >= 0 ? '+' : '-').'Rp '.number_format(abs($r->signed_amount), 0, ',', '.')),
                TextColumn::make('balance_after')->label('Saldo Akhir')->money('IDR'),
                TextColumn::make('reason')->label('Alasan')->limit(40)->default('-'),
                TextColumn::make('method')->label('Metode')->default('-')
                    ->formatStateUsing(fn ($s) => InvoicePayment::METHODS[$s] ?? $s),
                TextColumn::make('recordedBy.name')->label('Dicatat Oleh')->default('-')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis Mutasi')->options(DepositTransaction::TYPES),
                SelectFilter::make('deposit_id')->label('Deposit')->options(
                    fn () => Deposit::with('tenant')->get()->mapWithKeys(
                        fn ($d) => [$d->id => ($d->tenant?->name ?? 'Tenant #'.$d->tenant_id).' — Rp '.number_format($d->amount, 0, ',', '.')]
                    )
                )->searchable(),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->defaultSort('occurred_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepositTransactions::route('/'),
            'view' => Pages\ViewDepositTransaction::route('/{record}'),
        ];
    }
}
