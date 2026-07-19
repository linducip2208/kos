<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Models\Property;
use App\Models\RoomType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model           = Room::class;
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-home';
    protected static ?int    $navigationSort  = 20;

    public static function getNavigationGroup(): ?string { return '?? Properti & Kamar'; }
    public static function getLabel(): ?string            { return __('navigation.room'); }
    public static function getPluralLabel(): ?string      { return __('navigation.rooms'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kamar')
                ->schema([
                    Grid::make(3)->schema([
                        Select::make('property_id')
                            ->label('Properti')
                            ->options(Property::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('room_type_id', null)),

                        Select::make('room_type_id')
                            ->label('Tipe Kamar (opsional)')
                            ->options(fn (Get $get) => RoomType::where('property_id', $get('property_id'))->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->helperText('Pilih tipe untuk mengisi harga & deskripsi otomatis')
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (!$state) return;
                                $type = RoomType::find($state);
                                if (!$type) return;
                                if (!$get('price_daily'))     $set('price_daily', $type->base_price_daily);
                                if (!$get('price_weekly'))    $set('price_weekly', $type->base_price_weekly);
                                if (!$get('price_monthly'))   $set('price_monthly', $type->base_price_monthly);
                                if (!$get('price_quarterly')) $set('price_quarterly', $type->base_price_quarterly);
                                if (!$get('price_yearly'))    $set('price_yearly', $type->base_price_yearly);
                                if (!$get('description'))     $set('description', $type->description);
                                if (!$get('size_sqm'))        $set('size_sqm', $type->size_sqm);
                            }),

                        TextInput::make('room_number')
                            ->label('Nomor Kamar')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('A1, B-02, 101, dll'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Kamar')
                            ->placeholder('Kamar Deluxe Lantai 2')
                            ->helperText('Nama deskriptif untuk ditampilkan ke penyewa'),
                        TextInput::make('floor')->label('Lantai')->numeric()->minValue(0)->maxValue(99),
                    ]),
                ]),

            Section::make('Foto Kamar')->schema([
                FileUpload::make('photos')
                    ->label('Upload Foto Kamar (opsional, override foto tipe)')
                    ->image()->multiple()->reorderable()->maxFiles(6)
                    ->directory('rooms')->imagePreviewHeight('100')->panelLayout('grid')
                    ->columnSpanFull(),
            ]),

            Section::make('Deskripsi & Fasilitas Kamar')
                ->description('Setiap kamar bisa punya deskripsi & fasilitas berbeda dari tipe kamarnya')
                ->schema([
                    Textarea::make('description')
                        ->label('Deskripsi Kamar')
                        ->rows(3)
                        ->placeholder('Kamar luas dengan pemandangan taman...')
                        ->helperText('Kosongkan untuk menggunakan deskripsi dari tipe kamar'),
                    TagsInput::make('facilities')
                        ->label('Fasilitas Khusus Kamar')
                        ->suggestions(['AC', 'WiFi', 'Kamar Mandi Dalam', 'Lemari', 'Meja Belajar', 'TV', 'Kulkas', 'Water Heater', 'Balkon', 'Parkir Motor'])
                        ->helperText('Akan digabung dengan fasilitas dari tipe kamar'),
                    TextInput::make('size_sqm')->label('Luas (m²)')->numeric()->minValue(0)->suffix('m²'),
                ]),

            Section::make('Harga Per Kamar')
                ->description('Override harga dari tipe kamar. Kosongkan untuk menggunakan harga tipe.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('price_daily')->label('Harga Harian')->numeric()->prefix('Rp')
                            ->placeholder('Kosong = pakai harga tipe'),
                        TextInput::make('price_weekly')->label('Harga Mingguan')->numeric()->prefix('Rp')
                            ->placeholder('Kosong = pakai harga tipe'),
                    ]),
                    Grid::make(3)->schema([
                        TextInput::make('price_monthly')->label('Harga Bulanan')->numeric()->prefix('Rp')
                            ->placeholder('Kosong = pakai harga tipe'),
                        TextInput::make('price_quarterly')->label('Harga 3 Bulan')->numeric()->prefix('Rp')
                            ->placeholder('Kosong = pakai harga tipe'),
                        TextInput::make('price_yearly')->label('Harga Tahunan')->numeric()->prefix('Rp')
                            ->placeholder('Kosong = pakai harga tipe'),
                    ]),
                ]),

            Section::make('Status & Catatan')->schema([
                Grid::make(2)->schema([
                    Select::make('status')->label('Status')->options([
                        'available'   => 'Tersedia',
                        'occupied'    => 'Terisi',
                        'maintenance' => 'Maintenance',
                        'reserved'    => 'Dipesan',
                    ])->required(),
                    Toggle::make('is_active')->label('Aktif')->default(true),
                ]),
                Textarea::make('notes')->label('Catatan Internal')->rows(2)
                    ->placeholder('Catatan untuk staff, tidak terlihat penyewa'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('property.name')->label('Properti')->sortable()->searchable(),
                TextColumn::make('room_number')->label('No. Kamar')->sortable()->searchable()->weight('bold'),
                TextColumn::make('name')->label('Nama')->placeholder('-')->searchable(),
                TextColumn::make('roomType.name')->label('Tipe')->placeholder('Manual')->badge()->color('info'),
                TextColumn::make('effective_price_monthly')
                    ->label('Harga/Bulan')
                    ->getStateUsing(fn (Room $record) => $record->effective_price_monthly)
                    ->money('IDR'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'available'   => 'success',
                        'occupied'    => 'danger',
                        'maintenance' => 'warning',
                        'reserved'    => 'info',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'available'   => 'Tersedia',
                        'occupied'    => 'Terisi',
                        'maintenance' => 'Maintenance',
                        'reserved'    => 'Dipesan',
                        default       => $state,
                    }),
                TextColumn::make('floor')->label('Lantai')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('property_id')->label('Properti')
                    ->options(Property::pluck('name', 'id'))->searchable(),
                SelectFilter::make('room_type_id')->label('Tipe Kamar')
                    ->options(RoomType::pluck('name', 'id'))->searchable(),
                SelectFilter::make('status')->options([
                    'available'   => 'Tersedia',
                    'occupied'    => 'Terisi',
                    'maintenance' => 'Maintenance',
                    'reserved'    => 'Dipesan',
                ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('view_lease')
                    ->label('Kontrak')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Room $record) => $record->activeLease
                        ? LeaseResource::getUrl('edit', ['record' => $record->activeLease])
                        : LeaseResource::getUrl('create')
                    )
                    ->color(fn (Room $record) => $record->activeLease ? 'success' : 'gray'),
            ])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])])
            ->defaultSort('room_number');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit'   => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
