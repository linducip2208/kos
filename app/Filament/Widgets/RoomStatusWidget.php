<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\Room;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RoomStatusWidget extends BaseWidget
{
    protected static ?int $sort            = 2;
    protected static ?string $heading      = 'Status Kamar';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::with(['property', 'activeLease.occupant'])
                    ->where('is_active', true)
                    ->orderBy('status')
            )
            ->columns([
                TextColumn::make('property.name')->label('Properti')->sortable(),
                TextColumn::make('room_number')->label('Kamar')->weight('bold'),
                TextColumn::make('name')->label('Nama')->placeholder('-'),
                TextColumn::make('effective_price_monthly')
                    ->label('Harga/Bulan')
                    ->getStateUsing(fn (Room $record) => $record->effective_price_monthly)
                    ->money('IDR'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'available',
                        'danger'  => 'occupied',
                        'warning' => 'maintenance',
                        'info'    => 'reserved',
                    ])
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'available' => 'Tersedia', 'occupied' => 'Terisi',
                        'maintenance' => 'Maintenance', 'reserved' => 'Dipesan', default => $s,
                    }),
                TextColumn::make('activeLease.occupant.name')
                    ->label('Penyewa')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('activeLease.end_date')
                    ->label('Kontrak s/d')
                    ->date('d/m/Y')
                    ->placeholder('—'),
            ])
            ->paginated([10, 25, 50]);
    }
}
