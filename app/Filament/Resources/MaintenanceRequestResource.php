<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\MaintenanceRequestResource\Pages;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Models\Vendor;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceRequestResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = MaintenanceRequest::class;

    protected static array $tablePreloads = [
        'room',
            'occupant',
            'assignedTo',
            'vendor',
    ];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::OPERATIONAL;
    }

    public static function getLabel(): ?string
    {
        return 'Permintaan Maintenance';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Maintenance';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Kerusakan')->schema([
                Grid::make(2)->schema([
                    Select::make('room_id')->label('Kamar')
                        ->options(fn () => Room::with('property')->get()->mapWithKeys(
                            fn ($r) => [$r->id => ($r->property?->name ?? '-').' - '.$r->room_number]
                        ))->searchable()->required(),
                    Select::make('priority')->label('Prioritas')
                        ->options(\App\Models\MaintenanceRequest::PRIORITIES)
                        ->default('medium')->required(),
                ]),
                TextInput::make('title')->label('Judul Kerusakan')->required(),
                Textarea::make('description')->label('Deskripsi')->rows(3)->required(),
                FileUpload::make('photos')->label('Foto Kerusakan')->image()->multiple()->maxFiles(5)->disk('public')->visibility('public')
                    ->directory('maintenance/before'),
            ]),

            Section::make('Penanganan')->schema([
                Grid::make(2)->schema([
                    Select::make('status')->label('Status')
                        ->options(\App\Models\MaintenanceRequest::STATUSES)->default('open')->required(),
                    Select::make('assigned_to')->label('Ditugaskan ke')
                        ->options(User::pluck('name', 'id'))->searchable()->nullable(),
                ]),
                Grid::make(2)->schema([
                    Select::make('vendor_id')->label('Vendor Eksternal')
                        ->options(fn () => Vendor::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->nullable()
                        ->createOptionForm([
                            Grid::make(2)->schema([
                                TextInput::make('name')->label('Nama Vendor')->required(),
                                TextInput::make('phone')->label('No. HP'),
                                TextInput::make('contact_person')->label('Narahubung'),
                            ]),
                        ])
                        ->createOptionUsing(fn (array $data) => Vendor::create(['name' => $data['name'], 'phone' => $data['phone'] ?? null, 'contact_person' => $data['contact_person'] ?? null, 'is_active' => true])->id),
                    Select::make('category')->label('Kategori')->options(\App\Models\MaintenanceRequest::CATEGORIES)->default('general'),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('estimated_cost')->label('Estimasi Biaya')->numeric()->prefix('Rp'),
                    TextInput::make('actual_cost')->label('Biaya Aktual')->numeric()->prefix('Rp'),
                    DateTimePicker::make('sla_due_at')->label('Batas SLA'),
                ]),
                Textarea::make('resolution_notes')->label('Catatan Penyelesaian')->rows(2),
                FileUpload::make('resolution_photos')->label('Foto Sesudah')->image()->multiple()->maxFiles(5)->disk('public')->visibility('public')
                    ->directory('maintenance/after'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => ($record->room?->property?->name ?? '-').' - '.($record->room?->room_number ?? '-'))
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
                        'completed' => 'success', default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Terbuka', 'in_progress' => 'Dikerjakan', 'waiting_parts' => 'Tunggu Part',
                        'completed' => 'Selesai', 'cancelled' => 'Batal', default => $state,
                    }),
                TextColumn::make('assignedTo.name')->label('Teknisi')->default('-'),
                TextColumn::make('vendor.name')->label('Vendor')->default('-'),
                TextColumn::make('actual_cost')->label('Biaya')->money('IDR')->default('—'),
                TextColumn::make('created_at')->label('Dilaporkan')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(\App\Models\MaintenanceRequest::STATUSES),
                SelectFilter::make('vendor_id')->label('Vendor')
                    ->options(fn () => Vendor::pluck('name', 'id'))->searchable(),
                SelectFilter::make('priority')->label('Prioritas')->options(\App\Models\MaintenanceRequest::PRIORITIES),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->bulkActions([Actions\BulkActionGroup::make([Actions\DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceRequests::route('/'),
            'create' => Pages\CreateMaintenanceRequest::route('/create'),
            'edit' => Pages\EditMaintenanceRequest::route('/{record}/edit'),
        ];
    }
}
