<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 90;

    public static function getNavigationGroup(): ?string { return '⚙️ Sistem'; }
    public static function getLabel(): ?string { return 'Log Aktivitas'; }
    public static function getPluralLabel(): ?string { return 'Log Aktivitas'; }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->width(60),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('action')->label('Aksi')->badge(),
                TextColumn::make('model_type')->label('Model')->searchable(),
                TextColumn::make('model_id')->label('ID Model')->width(80),
                TextColumn::make('description')->label('Deskripsi')->limit(60),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view'  => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
