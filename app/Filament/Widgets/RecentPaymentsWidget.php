<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected static ?string $heading = 'Tagihan Terbaru & Tunggakan';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Tagihan Terbaru & Tunggakan')
            ->description('Invoice terkini yang diurutkan berdasarkan urgensi pembayaran.')
            ->query(
                Invoice::with(['lease.occupant', 'lease.room'])
                    ->whereIn('status', ['sent', 'overdue', 'paid'])
                    ->orderByRaw("CASE WHEN status = 'overdue' THEN 0 WHEN status = 'sent' THEN 1 ELSE 2 END")
                    ->orderBy('due_date')
                    ->limit(15)
            )
            ->columns([
                TextColumn::make('invoice_number')->label('No. Invoice')->weight('bold'),
                TextColumn::make('lease.occupant.name')->label('Penyewa'),
                TextColumn::make('lease.room.room_number')->label('Kamar'),
                TextColumn::make('total')->label('Total')->money('IDR'),
                TextColumn::make('due_date')->label('Jatuh Tempo')->date('d/m/Y')
                    ->color(fn (Invoice $r) => $r->is_overdue ? 'danger' : null),
                BadgeColumn::make('status')->colors([
                    'info' => 'sent', 'danger' => 'overdue', 'success' => 'paid',
                ])->formatStateUsing(fn ($state) => match ($state) {
                    'sent' => 'Terkirim', 'overdue' => 'Tunggakan', 'paid' => 'Lunas', default => $state,
                }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Tidak ada tagihan tertunda')
            ->emptyStateDescription('Semua invoice sudah lunas. Tagihan baru akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
