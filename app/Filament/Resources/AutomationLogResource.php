<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AutomationLogResource\Pages;
use App\Models\AutomationLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AutomationLogResource extends Resource
{
    protected static ?string $model = AutomationLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static ?int $navigationSort = 85;

    public static function getNavigationGroup(): ?string { return '⚙️ Sistem'; }
    public static function getLabel(): ?string { return 'Log Otomatis'; }
    public static function getPluralLabel(): ?string { return 'Log Otomatisasi'; }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
                TextColumn::make('rule_key')->label('Rule')->badge()->color('info')->searchable(),
                TextColumn::make('channel')->label('Kanal')->badge(),
                TextColumn::make('subject_type')->label('Subjek')
                    ->formatStateUsing(fn ($s) => $s ? class_basename($s) : '-')
                    ->description(fn (AutomationLog $record) => $record->recipient ?? ''),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'success' => 'success', 'triggered' => 'info',
                        'failed' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn ($s) => ucfirst($s)),
                TextColumn::make('attempts')->label('Percobaan')->alignCenter(),
                TextColumn::make('message')->label('Pesan')->limit(50)->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'triggered' => 'Triggered', 'success' => 'Success',
                    'failed' => 'Failed', 'skipped' => 'Skipped',
                ]),
                SelectFilter::make('channel')->label('Kanal')->options([
                    'whatsapp' => 'WhatsApp', 'email' => 'Email', 'database' => 'Database',
                ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomationLogs::route('/'),
            'view' => Pages\ViewAutomationLog::route('/{record}'),
        ];
    }
}
