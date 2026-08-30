<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\UtilityRateResource\Pages;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\UtilityRate;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UtilityRateResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = UtilityRate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 22;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::FINANCE;
    }

    public static function getLabel(): ?string
    {
        return 'Tarif Utilitas';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Tarif Utilitas';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cakupan Tarif')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('utility_type')->label('Jenis Utilitas')
                        ->options(UtilityRate::UTILITY_TYPES)->default('electricity')->required(),
                    Forms\Components\Select::make('scope')->label('Cakupan')
                        ->options(UtilityRate::SCOPES)->default('global')->required()->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('property_id', null);
                            $set('room_type_id', null);
                            $set('room_id', null);
                        }),
                    Forms\Components\Select::make('property_id')->label('Properti')
                        ->options(Property::pluck('name', 'id'))->searchable()
                        ->visible(fn (Get $get) => in_array($get('scope'), ['property', 'room']))
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('room_id', null)),
                    Forms\Components\Select::make('room_type_id')->label('Tipe Kamar')
                        ->options(RoomType::pluck('name', 'id'))->searchable()
                        ->visible(fn (Get $get) => $get('scope') === 'room_type'),
                    Forms\Components\Select::make('room_id')->label('Kamar')
                        ->options(fn (Get $get) => Room::query()
                            ->when($get('property_id'), fn ($q, $pid) => $q->where('property_id', $pid))
                            ->with('property')->get()->mapWithKeys(
                                fn ($r) => [$r->id => $r->property->name.' - '.$r->room_number]
                            ))
                        ->searchable()
                        ->visible(fn (Get $get) => $get('scope') === 'room'),
                ]),
            ]),

            Section::make('Rincian Biaya')->schema([
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('rate_per_unit')->label('Tarif per Unit (Rp)')->numeric()->prefix('Rp')->step(0.0001)->required(),
                    Forms\Components\TextInput::make('fixed_charge')->label('Biaya Tetap (Rp)')->numeric()->prefix('Rp')->default(0),
                    Forms\Components\TextInput::make('admin_charge')->label('Biaya Admin (Rp)')->numeric()->prefix('Rp')->default(0),
                    Forms\Components\TextInput::make('minimum_charge')->label('Tagihan Minimum (Rp)')->numeric()->prefix('Rp')->default(0),
                    Forms\Components\TextInput::make('minimum_usage')->label('Pemakaian Minimum (unit)')->numeric()->default(0),
                    Forms\Components\DatePicker::make('effective_from')->label('Berlaku Dari')->default(today())->required(),
                ]),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('utility_type')->label('Utilitas')->badge()
                    ->color(fn ($state) => $state === 'electricity' ? 'warning' : 'info')
                    ->formatStateUsing(fn ($s) => UtilityRate::UTILITY_TYPES[$s] ?? $s),
                TextColumn::make('scope_label')->label('Cakupan')->weight('bold'),
                TextColumn::make('rate_per_unit')->label('Tarif/Unit')->money('IDR'),
                TextColumn::make('fixed_charge')->label('Tetap')->money('IDR')->toggleable(),
                TextColumn::make('admin_charge')->label('Admin')->money('IDR')->toggleable(),
                TextColumn::make('minimum_charge')->label('Min. Tagihan')->money('IDR')->toggleable(),
                TextColumn::make('effective_from')->label('Berlaku Dari')->date('d M Y')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('utility_type')->label('Jenis')->options(UtilityRate::UTILITY_TYPES),
                SelectFilter::make('scope')->label('Cakupan')->options(UtilityRate::SCOPES),
                TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('effective_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUtilityRates::route('/'),
            'create' => Pages\CreateUtilityRate::route('/create'),
            'edit' => Pages\EditUtilityRate::route('/{record}/edit'),
        ];
    }
}
