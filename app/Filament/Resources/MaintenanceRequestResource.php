<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

class MaintenanceRequestResource extends Resource
{
    protected static ?string $model         = MaintenanceRequest::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int    $navigationSort = 10;

    public static function getNavigationGroup(): ?string { return __('navigation.group_property_room'); }
    public static function getLabel(): ?string           { return 'Permintaan Maintenance'; }
    public static function getPluralLabel(): ?string     { return 'Maintenance'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Kerusakan')->schema([
                Grid::make(2)->schema([
                    Select::make('room_id')->label('Kamar')
                        ->options(Room::with('property')->get()->mapWithKeys(
                            fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number]
                        ))->searchable()->required(),
                    Select::make('priority')->label('Prioritas')
                        ->options(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Urgent'])
                        ->default('medium')->required(),
                ]),
                TextInput::make('title')->label('Judul Kerusakan')->required(),
                Textarea::make('description')->label('Deskripsi')->rows(3)->required(),
                FileUpload::make('photos')->label('Foto Kerusakan')->image()->multiple()->maxFiles(5)
                    ->directory('maintenance/before'),
            ]),

            Section::make('Penanganan')->schema([
                Grid::make(2)->schema([
                    Select::make('status')->label('Status')
                        ->options([
                            'open' => 'Terbuka', 'in_progress' => 'Dikerjakan',
                            'waiting_parts' => 'Tunggu Part', 'resolved' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ])->default('open')->required(),
                    Select::make('assigned_to')->label('Ditugaskan ke')
                        ->options(User::pluck('name', 'id'))->searchable()->nullable(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('estimated_cost')->label('Estimasi Biaya')->numeric()->prefix('Rp'),
                    TextInput::make('actual_cost')->label('Biaya Aktual')->numeric()->prefix('Rp'),
                ]),
                Textarea::make('resolution_notes')->label('Catatan Penyelesaian')->rows(2),
                FileUpload::make('resolution_photos')->label('Foto Sesudah')->image()->multiple()->maxFiles(5)
                    ->directory('maintenance/after'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room->property->name . ' - ' . $record->room->room_number)
                    ->searchable()->sortable(),
                TextColumn::make('title')->label('Kerusakan')->searchable()->limit(40),
                TextColumn::make('priority')->label('Prioritas')->badge()
                    ->color(fn ($state) => match ($state) {
                        'urgent' => 'danger', 'high' => 'warning', 'medium' => 'info', default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'urgent' => 'Urgent', 'high' => 'Tinggi', 'medium' => 'Sedang', default => 'Rendah',
                    }),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'open' => 'danger', 'in_progress' => 'warning', 'waiting_parts' => 'info',
                        'resolved' => 'success', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Terbuka', 'in_progress' => 'Dikerjakan', 'waiting_parts' => 'Tunggu Part',
                        'resolved' => 'Selesai', 'cancelled' => 'Batal', default => $state,
                    }),
                TextColumn::make('assignedTo.name')->label('Teknisi')->default('-'),
                TextColumn::make('actual_cost')->label('Biaya')->money('IDR')->default('—'),
                TextColumn::make('created_at')->label('Dilaporkan')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'open' => 'Terbuka', 'in_progress' => 'Dikerjakan',
                    'waiting_parts' => 'Tunggu Part', 'resolved' => 'Selesai',
                ]),
                SelectFilter::make('priority')->options([
                    'urgent' => 'Urgent', 'high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah',
                ]),
            ])
            ->actions([Actions\EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaintenanceRequests::route('/'),
            'create' => Pages\CreateMaintenanceRequest::route('/create'),
            'edit'   => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
