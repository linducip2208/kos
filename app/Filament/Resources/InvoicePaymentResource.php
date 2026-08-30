<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\InvoicePaymentResource\Pages;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Services\PaymentService;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicePaymentResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = InvoicePayment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 15;

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
        return 'Pembayaran Invoice';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pembayaran')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('invoice_id')->label('Invoice')
                        ->options(Invoice::with('lease.occupant')->get()->pluck('invoice_number', 'id'))
                        ->searchable()->required()->disabled(),
                    Forms\Components\TextInput::make('amount')->label('Nominal')->numeric()->prefix('Rp')->required(),
                    Forms\Components\Select::make('method')->label('Metode')
                        ->options(InvoicePayment::METHODS)->default('cash')->required(),
                    Forms\Components\TextInput::make('reference')->label('Referensi'),
                ]),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paid_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('invoice.invoice_number')->label('Invoice')->searchable(),
                TextColumn::make('invoice.lease.occupant.name')->label('Penyewa')->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($s) => $s === 'refund' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($s) => $s === 'refund' ? 'Refund' : 'Bayar'),
                TextColumn::make('amount')->label('Nominal')->money('IDR')->sortable()->weight('bold'),
                TextColumn::make('method')->label('Metode')
                    ->formatStateUsing(fn ($s) => InvoicePayment::METHODS[$s] ?? $s),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'verified' => 'success', 'pending_verification' => 'warning',
                        'rejected' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'verified' => 'Terverifikasi', 'pending_verification' => 'Menunggu Verifikasi',
                        'rejected' => 'Ditolak', default => $s,
                    }),
                TextColumn::make('reference')->label('Referensi')->default('-')->limit(20)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('invoice_id')->label('Invoice')->options(
                    fn () => Invoice::with('lease.occupant')->get()->pluck('invoice_number', 'id')
                )->searchable(),
                SelectFilter::make('status')->label('Status')->options([
                    'verified' => 'Terverifikasi', 'pending_verification' => 'Menunggu Verifikasi', 'rejected' => 'Ditolak',
                ]),
                SelectFilter::make('method')->label('Metode')->options(InvoicePayment::METHODS),
                SelectFilter::make('type')->label('Jenis')->options(['payment' => 'Bayar', 'refund' => 'Refund']),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\Action::make('verify')
                    ->label('Verifikasi')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (InvoicePayment $r) => $r->status === 'pending_verification')
                    ->requiresConfirmation()
                    ->modalDescription('Konfirmasi bukti pembayaran ini sudah diterima dan valid?')
                    ->action(function (InvoicePayment $record) {
                        app(PaymentService::class)->verify($record, auth()->user());
                        Notification::make()->title('Pembayaran terverifikasi.')->success()->send();
                    }),
                Actions\Action::make('reject')
                    ->label('Tolak')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (InvoicePayment $r) => $r->status === 'pending_verification')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('Alasan Penolakan')->required()->rows(2),
                    ])
                    ->action(function (InvoicePayment $record, array $data) {
                        app(PaymentService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Pembayaran ditolak.')->warning()->send();
                    }),
                Actions\EditAction::make()
                    ->visible(fn (InvoicePayment $r) => in_array($r->status, ['verified', 'pending_verification'])),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoicePayments::route('/'),
            'edit' => Pages\EditInvoicePayment::route('/{record}/edit'),
            'view' => Pages\ViewInvoicePayment::route('/{record}'),
        ];
    }
}
