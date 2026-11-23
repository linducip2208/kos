<?php

namespace App\Filament\Widgets;

use App\Models\InvoicePayment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingVerificationsWidget extends BaseWidget
{
    protected static ?int $sort = 9;

    protected static ?string $heading = 'Pembayaran Menunggu Verifikasi';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return InvoicePayment::where('status', 'pending_verification')->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pembayaran Menunggu Verifikasi')
            ->description('Pembayaran masuk yang perlu diverifikasi sebelum dicatat sebagai kas.')
            ->query(
                InvoicePayment::query()
                    ->where('status', 'pending_verification')
                    ->with(['invoice.lease.occupant', 'invoice.lease.room'])
                    ->orderBy('paid_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('paid_at')->label('Tanggal')->dateTime('d/m/Y H:i'),
                TextColumn::make('invoice.invoice_number')->label('Invoice'),
                TextColumn::make('invoice.lease.occupant.name')->label('Penyewa'),
                TextColumn::make('amount')->label('Nominal')->money('IDR')->weight('bold'),
                TextColumn::make('method')->label('Metode')
                    ->formatStateUsing(fn ($s) => InvoicePayment::METHODS[$s] ?? $s),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada pembayaran menunggu')
            ->emptyStateDescription('Semua pembayaran sudah diverifikasi.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
