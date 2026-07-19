<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model           = Property::class;
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-building-office-2';
    protected static ?int    $navigationSort  = 10;

    public static function getNavigationGroup(): ?string { return '?? Properti & Kamar'; }
    public static function getLabel(): ?string            { return __('navigation.property'); }
    public static function getPluralLabel(): ?string      { return __('navigation.properties'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Properti')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama Properti')->required(),
                    TextInput::make('city')->label('Kota')->required(),
                    TextInput::make('province')->label('Provinsi')->required(),
                    TextInput::make('postal_code')->label('Kode Pos'),
                ]),
                Textarea::make('address')->label('Alamat Lengkap')->required()->rows(2)->columnSpanFull(),
                RichEditor::make('description')
                    ->label('Deskripsi Properti')
                    ->toolbarButtons(['bold','italic','bulletList','orderedList','h2','h3','link'])
                    ->columnSpanFull(),
            ]),

            Section::make('Foto Properti')->schema([
                FileUpload::make('photos')
                    ->label('Upload Foto (bisa lebih dari satu)')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(10)
                    ->directory('properties')
                    ->imagePreviewHeight('120')
                    ->panelLayout('grid')
                    ->columnSpanFull(),
            ]),

            Section::make('Lokasi & Fasilitas')->schema([
                Grid::make(2)->schema([
                    TextInput::make('latitude')->label('Latitude')->numeric()->placeholder('-6.200000'),
                    TextInput::make('longitude')->label('Longitude')->numeric()->placeholder('106.816666'),
                ]),
                TagsInput::make('facilities')
                    ->label('Fasilitas Umum')
                    ->suggestions(['WiFi Area', 'Parkir Motor', 'Parkir Mobil', 'Laundry', 'Dapur Bersama', 'CCTV', 'Security 24 Jam', 'Mushola', 'Air Panas', 'Kolam Renang', 'Gym'])
                    ->columnSpanFull(),
                Textarea::make('rules')->label('Peraturan Kos')->rows(4)->columnSpanFull(),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold'),
                TextColumn::make('city')->label('Kota')->sortable(),
                TextColumn::make('province')->label('Provinsi'),
                TextColumn::make('rooms_count')->label('Total Kamar')->counts('rooms')->badge()->color('info'),
                TextColumn::make('available_rooms_count')->label('Tersedia')->counts('availableRooms')->badge()->color('success'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('rooms')
                    ->label('Lihat Kamar')
                    ->icon('heroicon-o-home')
                    ->url(fn (Property $record) => RoomResource::getUrl('index') . '?tableFilters[property_id][value]=' . $record->id),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'edit'   => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
