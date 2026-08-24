<?php

namespace App\Filament\Widgets;

use App\Models\InvoicePayment;
use App\Services\PaymentService;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingVerificationsWidget extends BaseWidget
{
    protected static ?int $sort       = 4;
    protected static ?string $heading = 'Pembayaran Menunggu Verifikasi';

    public static function canView(): bool
    {
        return InvoicePayment::where('status', 'pending_verification')->exists();
    }

    public function table(Table $table): Table
    {
        return $table
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
            ->paginated(false);
    }
}
