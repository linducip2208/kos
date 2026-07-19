<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomChecklistResource\Pages;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use App\Models\RoomChecklist;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomChecklistResource extends Resource
{
    protected static ?string $model         = RoomChecklist::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int    $navigationSort = 35;

    public static function getNavigationGroup(): ?string { return __('navigation.group_property_room'); }
    public static function getLabel(): ?string           { return 'Checklist Kamar'; }
    public static function getPluralLabel(): ?string     { return 'Checklist Kamar'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Checklist')->schema([
                Grid::make(3)->schema([
                    Select::make('lease_id')->label('Kontrak Sewa')
                        ->options(
                            Lease::with(['occupant', 'room'])->get()
                                ->mapWithKeys(fn ($l) => [$l->id => ($l->occupant->name ?? '-') . ' — Kamar ' . ($l->room->room_number ?? '')])
                        )->searchable()->required(),
                    Select::make('room_id')->label('Kamar')
                        ->options(Room::with('property')->get()->mapWithKeys(
                            fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number]
                        ))->searchable()->required(),
                    Select::make('occupant_id')->label('Penyewa')
                        ->options(Occupant::pluck('name', 'id'))->searchable()->required(),
                ]),
                Grid::make(2)->schema([
                    Select::make('type')->label('Jenis')->options([
                        'check_in' => 'Check In', 'check_out' => 'Check Out',
                    ])->required(),
                    TextInput::make('signed_by')->label('Ditandatangani Oleh'),
                ]),
            ]),
            Section::make('Item Checklist')->schema([
                Repeater::make('items')->label('')->schema([
                    TextInput::make('item')->label('Item')->required(),
                    Select::make('condition')->label('Kondisi')->options([
                        'good' => 'Baik', 'fair' => 'Cukup', 'damaged' => 'Rusak', 'missing' => 'Hilang',
                    ])->default('good'),
                    TextInput::make('notes')->label('Catatan'),
                ])->defaultItems(0)->columns(3),
            ]),
            Section::make('Biaya & Deposit')->schema([
                Grid::make(2)->schema([
                    TextInput::make('damage_cost')->label('Biaya Kerusakan (Rp)')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('deposit_refund')->label('Pengembalian Deposit (Rp)')->numeric()->prefix('Rp')->default(0),
                ]),
                FileUpload::make('photos')->label('Foto Dokumentasi')->image()->multiple()->directory('checklists'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease.occupant.name')->label('Penyewa')->weight('bold'),
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => optional($record->room?->property)->name . ' - ' . optional($record->room)->room_number),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($state) => $state === 'check_in' ? 'success' : 'warning')
                    ->formatStateUsing(fn ($state) => $state === 'check_in' ? 'Check In' : 'Check Out'),
                TextColumn::make('damage_cost')->label('Biaya Kerusakan')->money('IDR'),
                TextColumn::make('deposit_refund')->label('Refund Deposit')->money('IDR'),
                TextColumn::make('signed_by')->label('TTD Oleh'),
                TextColumn::make('created_at')->label('Tanggal')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['check_in' => 'Check In', 'check_out' => 'Check Out']),
            ])
            ->actions([
                Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoomChecklists::route('/'),
            'create' => Pages\CreateRoomChecklist::route('/create'),
            'edit'   => Pages\EditRoomChecklist::route('/{record}/edit'),
        ];
    }
}
