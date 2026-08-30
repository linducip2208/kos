<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\RoomInventoryItemResource\Pages;
use App\Models\Room;
use App\Models\RoomInventoryItem;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomInventoryItemResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = RoomInventoryItem::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 45;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::OPERATIONAL;
    }

    public static function getLabel(): ?string
    {
        return 'Inventaris Kamar';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Inventaris Kamar';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Item Inventaris')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('room_id')->label('Kamar')
                        ->options(Room::with('property')->get()->mapWithKeys(
                            fn ($r) => [$r->id => $r->property->name.' - '.$r->room_number]
                        ))->searchable()->required(),
                    Forms\Components\TextInput::make('name')->label('Nama Item')->required()->maxLength(255),
                    Forms\Components\Select::make('category')->label('Kategori')->options([
                        'furniture' => 'Furniture',
                        'electronic' => 'Elektronik',
                        'bedding' => 'Bedding',
                        'kitchen' => 'Dapur',
                        'bathroom' => 'Kamar Mandi',
                        'other' => 'Lainnya',
                    ])->default('furniture')->required(),
                    Forms\Components\TextInput::make('serial_number')->label('No. Seri')->nullable(),
                    Forms\Components\TextInput::make('quantity')->label('Jumlah')->numeric()->minValue(1)->default(1)->required(),
                    Forms\Components\DatePicker::make('acquired_at')->label('Tanggal Perolehan'),
                    Forms\Components\Select::make('condition')->label('Kondisi')
                        ->options(RoomInventoryItem::CONDITIONS)->default('good')->required(),
                    Forms\Components\TextInput::make('replacement_value')->label('Nilai Ganti (Rp)')->numeric()->prefix('Rp')->default(0),
                ]),
                Forms\Components\FileUpload::make('photo')->label('Foto')->image()->directory('inventory')->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room?->property?->name.' - '.$record->room?->room_number)
                    ->searchable()->sortable(),
                TextColumn::make('name')->label('Item')->searchable()->weight('bold'),
                TextColumn::make('category')->label('Kategori')->badge()
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'furniture' => 'Furniture', 'electronic' => 'Elektronik', 'bedding' => 'Bedding',
                        'kitchen' => 'Dapur', 'bathroom' => 'KM', default => 'Lainnya',
                    }),
                TextColumn::make('quantity')->label('Qty')->alignCenter(),
                TextColumn::make('condition')->label('Kondisi')->badge()
                    ->color(fn ($state) => match ($state) {
                        'good' => 'success', 'fair' => 'info', 'poor' => 'warning',
                        'broken' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => RoomInventoryItem::CONDITIONS[$state] ?? $state),
                TextColumn::make('replacement_value')->label('Nilai Ganti')->money('IDR')->sortable(),
            ])
            ->filters([
                SelectFilter::make('condition')->label('Kondisi')->options(RoomInventoryItem::CONDITIONS),
                SelectFilter::make('room_id')->label('Kamar')->options(
                    fn () => Room::with('property')->get()->mapWithKeys(
                        fn ($r) => [$r->id => $r->property->name.' - '.$r->room_number]
                    )
                )->searchable(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('room_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoomInventoryItems::route('/'),
            'create' => Pages\CreateRoomInventoryItem::route('/create'),
            'edit' => Pages\EditRoomInventoryItem::route('/{record}/edit'),
        ];
    }
}
