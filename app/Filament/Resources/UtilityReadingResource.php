<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtilityReadingResource\Pages;
use App\Models\Room;
use App\Models\UtilityReading;
use App\Services\UtilityService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UtilityReadingResource extends Resource
{
    protected static ?string $model         = UtilityReading::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';
    protected static ?int    $navigationSort = 20;

    public static function getNavigationGroup(): ?string { return '?? Keuangan'; }
    public static function getLabel(): ?string           { return 'Meteran'; }
    public static function getPluralLabel(): ?string     { return 'Meteran Utilitas'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pembacaan')->schema([
                Grid::make(3)->schema([
                    Select::make('room_id')->label('Kamar')
                        ->options(Room::with('property')->get()->mapWithKeys(
                            fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number]
                        ))->searchable()->required()->live()
                        ->afterStateUpdated(fn (Set $set, Get $get) => static::fillPreviousReading($set, $get)),
                    Select::make('type')->label('Jenis')
                        ->options(['electricity' => 'Listrik (kWh)', 'water' => 'Air (m³)', 'gas' => 'Gas (m³)'])
                        ->default('electricity')->required()->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $set('rate_per_unit', app(UtilityService::class)->getDefaultRate($get('type') ?? 'electricity'));
                            static::fillPreviousReading($set, $get);
                        }),
                    DatePicker::make('billing_period')->label('Periode Tagihan')
                        ->displayFormat('M Y')->format('Y-m-01')->default(now()->startOfMonth())->required(),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('previous_reading')->label('Bacaan Sebelumnya')->numeric()->disabled()->dehydrated(),
                    TextInput::make('current_reading')->label('Bacaan Sekarang')->numeric()->required()
                        ->live(debounce: 500)->afterStateUpdated(fn (Get $get, Set $set) => static::calcAmount($get, $set)),
                    TextInput::make('rate_per_unit')->label('Tarif/Unit (Rp)')->numeric()->prefix('Rp')
                        ->default(fn () => app(UtilityService::class)->getDefaultRate('electricity'))
                        ->live(debounce: 500)->afterStateUpdated(fn (Get $get, Set $set) => static::calcAmount($get, $set)),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('amount')->label('Total Tagihan')->numeric()->prefix('Rp')->disabled()->dehydrated(),
                    DatePicker::make('reading_date')->label('Tanggal Baca')->default(now())->required(),
                ]),
                FileUpload::make('photo')->label('Foto Meteran')->image()->directory('utilities'),
            ]),
        ]);
    }

    private static function fillPreviousReading(Set $set, Get $get): void
    {
        $roomId = $get('room_id');
        $type   = $get('type');
        if ($roomId && $type) {
            $prev = UtilityReading::where('room_id', $roomId)->where('type', $type)
                ->orderByDesc('billing_period')->value('current_reading') ?? 0;
            $set('previous_reading', $prev);
        }
    }

    private static function calcAmount(Get $get, Set $set): void
    {
        $usage  = max(0, (float)($get('current_reading') ?? 0) - (float)($get('previous_reading') ?? 0));
        $rate   = (float)($get('rate_per_unit') ?? 0);
        $set('amount', $usage * $rate);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room->property->name . ' - ' . $record->room->room_number),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($state) => match ($state) { 'electricity' => 'warning', 'water' => 'info', default => 'success' })
                    ->formatStateUsing(fn ($state) => match ($state) { 'electricity' => 'Listrik', 'water' => 'Air', default => 'Gas' }),
                TextColumn::make('billing_period')->label('Periode')->date('M Y')->sortable(),
                TextColumn::make('previous_reading')->label('Sebelumnya')->numeric(1),
                TextColumn::make('current_reading')->label('Sekarang')->numeric(1),
                TextColumn::make('usage')->label('Pemakaian')->numeric(1)
                    ->getStateUsing(fn ($record) => $record->current_reading - $record->previous_reading),
                TextColumn::make('amount')->label('Tagihan')->money('IDR'),
                IconColumn::make('added_to_invoice')->label('Di Invoice')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->options(['electricity' => 'Listrik', 'water' => 'Air', 'gas' => 'Gas']),
                SelectFilter::make('added_to_invoice')->label('Status')->options([0 => 'Belum di-invoice', 1 => 'Sudah di-invoice']),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('add_to_invoice')
                    ->label('Tambah ke Invoice')->icon('heroicon-o-plus-circle')->color('success')
                    ->visible(fn (UtilityReading $r) => !$r->added_to_invoice && $r->lease_id)
                    ->action(function (UtilityReading $record) {
                        $invoice = $record->lease->invoices()->whereIn('status', ['draft','sent'])->latest()->first();
                        if (!$invoice) {
                            Notification::make()->title('Tidak ada invoice aktif untuk kontrak ini.')->warning()->send();
                            return;
                        }
                        app(\App\Services\UtilityService::class)->addToInvoice($record, $invoice);
                        Notification::make()->title('Tagihan utilitas ditambahkan ke invoice.')->success()->send();
                    }),
            ])
            ->defaultSort('billing_period', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUtilityReadings::route('/'),
            'create' => Pages\CreateUtilityReading::route('/create'),
            'edit'   => Pages\EditUtilityReading::route('/{record}/edit'),
        ];
    }
}
