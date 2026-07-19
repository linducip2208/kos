<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPaymentsWidget extends BaseWidget
{
    protected static ?int $sort       = 3;
    protected static ?string $heading = 'Tagihan Terbaru & Tunggakan';

    public function table(Table $table): Table
    {
        return $table
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
                ])->formatStateUsing(fn ($s) => match ($s) {
                    'sent' => 'Terkirim', 'overdue' => 'Tunggakan', 'paid' => 'Lunas', default => $s,
                }),
            ])
            ->paginated(false);
    }
}
