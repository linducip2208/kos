<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitorLogResource\Pages;
use App\Models\VisitorLog;
use App\Models\Occupant;
use App\Models\Property;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VisitorLogResource extends Resource
{
    protected static ?string $model = VisitorLog::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string { return 'Tamu'; }
    public static function getPluralLabel(): ?string { return 'Buku Tamu'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Tamu')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('property_id')->label('Properti')->options(Property::pluck('name', 'id'))->nullable(),
                    Forms\Components\Select::make('tenant_id')->label('Dituju (Penyewa)')->options(Occupant::pluck('name', 'id'))->nullable()->searchable(),
                    Forms\Components\TextInput::make('visitor_name')->label('Nama Tamu')->required(),
                    Forms\Components\TextInput::make('visitor_phone')->label('No. HP')->nullable(),
                    Forms\Components\TextInput::make('visitor_id_number')->label('No. KTP')->nullable(),
                    Forms\Components\TextInput::make('purpose')->label('Keperluan')->nullable(),
                    Forms\Components\DateTimePicker::make('check_in')->label('Jam Masuk')->required()->default(now()),
                    Forms\Components\DateTimePicker::make('check_out')->label('Jam Keluar')->nullable(),
                ]),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('check_in')->label('Masuk')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('visitor_name')->label('Tamu')->searchable(),
                TextColumn::make('visitor_phone')->label('No. HP'),
                TextColumn::make('tenant.name')->label('Menemui')->default('-'),
                TextColumn::make('purpose')->label('Keperluan')->limit(30),
                TextColumn::make('check_out')->label('Keluar')->dateTime('H:i')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('property_id')->label('Properti')->options(Property::pluck('name', 'id')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('check_in', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitorLogs::route('/'),
            'create' => Pages\CreateVisitorLog::route('/create'),
            'edit' => Pages\EditVisitorLog::route('/{record}/edit'),
        ];
    }
}
