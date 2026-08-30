<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\CheckinRecordResource\Pages;
use App\Models\CheckinRecord;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CheckinRecordResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = CheckinRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 40;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::OPERATIONAL;
    }

    public static function getLabel(): ?string
    {
        return 'Check-in/out';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Riwayat Check-in/out';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('completed_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($s) => $s === 'check_in' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($s) => CheckinRecord::TYPES[$s] ?? $s),
                TextColumn::make('occupant.name')->label('Penyewa')->searchable(),
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => ($record->room?->property?->name ? $record->room->property->name.' - ' : '').$record->room?->room_number),
                TextColumn::make('key_handover')->label('Kunci')
                    ->badge()->color(fn ($state) => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state ? 'Diserahkan' : 'Belum'),
                TextColumn::make('damage_amount')->label('Kerusakan')->money('IDR')->placeholder('-'),
                TextColumn::make('cleaning_amount')->label('Cleaning')->money('IDR')->placeholder('-'),
                TextColumn::make('tenant_payable')->label('Tenant Bayar')->money('IDR')
                    ->color('danger')->placeholder('-'),
                TextColumn::make('performedBy.name')->label('Petugas')->default('-')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(CheckinRecord::TYPES),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->defaultSort('completed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCheckinRecords::route('/'),
            'view' => Pages\ViewCheckinRecord::route('/{record}'),
        ];
    }
}
