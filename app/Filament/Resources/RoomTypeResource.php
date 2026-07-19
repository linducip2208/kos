<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomTypeResource\Pages;
use App\Models\Property;
use App\Models\RoomType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomTypeResource extends Resource
{
    protected static ?string $model          = RoomType::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-squares-2x2';
    protected static ?int    $navigationSort = 20;

    public static function getNavigationGroup(): ?string { return __('navigation.group_property_room'); }
    public static function getLabel(): ?string            { return __('navigation.room_type'); }
    public static function getPluralLabel(): ?string      { return __('navigation.room_types'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Info Tipe Kamar')->schema([
                Grid::make(2)->schema([
                    Select::make('property_id')
                        ->label('Properti')
                        ->options(Property::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('name')->label('Nama Tipe')->required()
                        ->placeholder('Standard, Deluxe, VIP, dsb.'),
                    TextInput::make('size_sqm')->label('Luas (m²)')->numeric()->suffix('m²'),
                    TextInput::make('max_occupants')->label('Maks. Penghuni')->numeric()->default(1)->minValue(1),
                ]),
                RichEditor::make('description')
                    ->label('Deskripsi Tipe Kamar')
                    ->toolbarButtons(['bold','italic','bulletList','orderedList'])
                    ->columnSpanFull(),
            ]),

            Section::make('Foto Kamar')->schema([
                FileUpload::make('photos')
                    ->label('Upload Foto Kamar (bisa lebih dari satu)')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(8)
                    ->directory('room-types')
                    ->imagePreviewHeight('120')
                    ->panelLayout('grid')
                    ->columnSpanFull(),
            ]),

            Section::make('Harga Dasar')
                ->description('Harga default untuk semua kamar bertipe ini. Bisa di-override per kamar.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('base_price_daily')->label('Harga Harian')->numeric()->prefix('Rp'),
                        TextInput::make('base_price_weekly')->label('Harga Mingguan')->numeric()->prefix('Rp'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('base_price_monthly')->label('Harga Bulanan')->numeric()->prefix('Rp')->required(),
                        TextInput::make('base_price_quarterly')->label('Harga Triwulan')->numeric()->prefix('Rp'),
                        TextInput::make('base_price_yearly')->label('Harga Tahunan')->numeric()->prefix('Rp'),
                    ]),
                ]),

            Section::make('Fasilitas')->schema([
                TagsInput::make('facilities')
                    ->label('Fasilitas Tipe Kamar')
                    ->suggestions([
                        'AC', 'Kipas Angin', 'Kasur', 'Lemari', 'Meja Belajar', 'Kursi',
                        'TV', 'Kulkas Mini', 'Kamar Mandi Dalam', 'Kamar Mandi Luar',
                        'Water Heater', 'Balkon', 'WiFi', 'Jendela', 'Cermin',
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name')->label('Properti')->sortable()->searchable(),
                TextColumn::make('name')->label('Tipe')->sortable()->searchable()->weight('bold'),
                TextColumn::make('size_sqm')->label('Luas')->suffix(' m²'),
                TextColumn::make('base_price_monthly')->label('Harga/Bulan')->money('IDR'),
                TextColumn::make('rooms_count')->label('Jumlah Kamar')->counts('rooms')->badge()->color('info'),
            ])
            ->filters([
                SelectFilter::make('property_id')
                    ->label('Properti')
                    ->options(Property::pluck('name', 'id')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('rooms')
                    ->label('Lihat Kamar')
                    ->icon('heroicon-o-home')
                    ->url(fn (RoomType $r) => RoomResource::getUrl('index') . '?tableFilters[room_type_id][value]=' . $r->id),
            ])
            ->defaultSort('property_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoomTypes::route('/'),
            'create' => Pages\CreateRoomType::route('/create'),
            'edit'   => Pages\EditRoomType::route('/{record}/edit'),
        ];
    }
}
