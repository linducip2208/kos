<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FacilityResource\Pages;
use App\Models\Facility;
use App\Models\Property;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static ?int $navigationSort = 35;

    public static function getNavigationGroup(): ?string { return '🏠 Properti & Kamar'; }
    public static function getLabel(): ?string { return 'Fasilitas'; }
    public static function getPluralLabel(): ?string { return 'Fasilitas'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama Fasilitas')->required(),
                    TextInput::make('icon')->label('Icon (emoji)')->nullable()->placeholder('🏊'),
                    Select::make('property_id')->label('Properti')
                        ->options(Property::pluck('name', 'id'))->nullable()->placeholder('Semua Properti'),
                ]),
                Textarea::make('description')->label('Deskripsi')->rows(2)->columnSpanFull(),
                Grid::make(2)->schema([
                    Toggle::make('is_active')->label('Aktif')->default(true),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width(50),
                TextColumn::make('icon')->label('')->width(40),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('property.name')->label('Properti')->default('Semua')->badge()->color('gray'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->reorderable('sort_order')->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit' => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
