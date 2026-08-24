<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomTransferResource\Pages;
use App\Models\RoomTransfer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomTransferResource extends Resource
{
    protected static ?string $model = RoomTransfer::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?int $navigationSort = 42;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string { return 'Pindah Kamar'; }
    public static function getPluralLabel(): ?string { return 'Riwayat Pindah Kamar'; }

    public static function canCreate(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('effective_date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('lease.lease_number')->label('Kontrak')->searchable(),
                TextColumn::make('lease.occupant.name')->label('Penyewa')->searchable(),
                TextColumn::make('fromRoom.room_number')->label('Dari Kamar')
                    ->formatStateUsing(fn ($record) => ($record->fromRoom?->property?->name ? $record->fromRoom->property->name.' - ' : '').$record->fromRoom?->room_number),
                TextColumn::make('toRoom.room_number')->label('Ke Kamar')
                    ->formatStateUsing(fn ($record) => ($record->toRoom?->property?->name ? $record->toRoom->property->name.' - ' : '').$record->toRoom?->room_number),
                TextColumn::make('prorate_amount')->label('Prorate')
                    ->badge()->color(fn (RoomTransfer $r) => $r->prorate_amount > 0 ? 'danger' : ($r->prorate_amount < 0 ? 'success' : 'gray'))
                    ->formatStateUsing(fn (RoomTransfer $r) => $r->prorate_label),
                Tables\Columns\IconColumn::make('final_utility_done')->label('Utilitas Final')->boolean()->toggleable(),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => $s === 'completed' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($s) => ucfirst($s)),
                TextColumn::make('performedBy.name')->label('Oleh')->default('-')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(['completed' => 'Completed', 'pending' => 'Pending']),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->defaultSort('effective_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoomTransfers::route('/'),
            'view' => Pages\ViewRoomTransfer::route('/{record}'),
        ];
    }
}
