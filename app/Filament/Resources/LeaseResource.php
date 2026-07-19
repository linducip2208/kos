<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaseResource\Pages;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

class LeaseResource extends Resource
{
    protected static ?string $model           = Lease::class;
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-document-text';
    protected static ?int    $navigationSort  = 20;

    public static function getNavigationGroup(): ?string { return '?? Penghuni & Sewa'; }
    public static function getLabel(): ?string            { return __('navigation.lease'); }
    public static function getPluralLabel(): ?string      { return __('navigation.leases'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kamar & Penyewa')->schema([
                Grid::make(2)->schema([
                    Select::make('room_id')
                        ->label('Kamar')
                        ->options(
                            Room::with('property')
                                ->whereIn('status', ['available', 'reserved'])
                                ->get()
                                ->mapWithKeys(fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number . ($r->name ? " ({$r->name})" : '')])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if (!$state) return;
                            $room = Room::find($state);
                            if ($room) $set('price', $room->effective_price_monthly);
                        }),

                    Select::make('occupant_id')
                        ->label('Penyewa')
                        ->options(Occupant::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            Grid::make(2)->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('phone')->required(),
                                TextInput::make('email'),
                                TextInput::make('whatsapp'),
                            ]),
                        ])
                        ->createOptionUsing(fn (array $data) => Occupant::create($data)->id),
                ]),
            ]),

            Section::make('Periode & Harga')->schema([
                Grid::make(3)->schema([
                    DatePicker::make('start_date')->label('Tanggal Masuk')->required(),
                    DatePicker::make('end_date')->label('Tanggal Keluar')->required(),
                    Select::make('billing_cycle')->label('Siklus Tagihan')
                        ->options([
                            'daily'     => 'Harian',
                            'weekly'    => 'Mingguan',
                            'monthly'   => 'Bulanan',
                            'quarterly' => 'Triwulan',
                            'yearly'    => 'Tahunan',
                        ])
                        ->default('monthly')->required(),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('price')->label('Harga Sewa')->numeric()->prefix('Rp')->required(),
                    TextInput::make('deposit')->label('Deposit')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('billing_date')->label('Tanggal Tagih (tgl)')->numeric()->minValue(1)->maxValue(28)->default(1),
                ]),
            ]),

            Section::make('Status')->schema([
                Grid::make(2)->schema([
                    Select::make('status')
                        ->options(['pending' => 'Pending', 'active' => 'Aktif', 'expired' => 'Berakhir', 'terminated' => 'Diterminasi'])
                        ->default('active')->required(),
                ]),
                Textarea::make('notes')->label('Catatan')->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease_number')->label('No. Kontrak')->searchable()->weight('bold'),
                TextColumn::make('occupant.name')->label('Penyewa')->searchable()->sortable(),
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room->property->name . ' - ' . $record->room->room_number),
                TextColumn::make('start_date')->label('Masuk')->date('d/m/Y')->sortable(),
                TextColumn::make('end_date')->label('Keluar')->date('d/m/Y')->sortable()
                    ->color(fn (Lease $record) => $record->is_expiring_soon ? 'warning' : null),
                TextColumn::make('price')->label('Harga')->money('IDR'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success', 'pending' => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif', 'pending' => 'Pending',
                        'expired' => 'Berakhir', 'terminated' => 'Diterminasi', default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('occupant_id')->label('Penyewa')
                    ->options(fn () => \App\Models\Occupant::pluck('name', 'id'))->searchable(),
                SelectFilter::make('status')->options([
                    'active' => 'Aktif', 'pending' => 'Pending',
                    'expired' => 'Berakhir', 'terminated' => 'Diterminasi',
                ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('invoices')
                    ->label('Tagihan')
                    ->icon('heroicon-o-banknotes')
                    ->url(fn (Lease $r) => InvoiceResource::getUrl('index') . '?tableFilters[lease_id][value]=' . $r->id),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeases::route('/'),
            'create' => Pages\CreateLease::route('/create'),
            'edit'   => Pages\EditLease::route('/{record}/edit'),
        ];
    }
}
