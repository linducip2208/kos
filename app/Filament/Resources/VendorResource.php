<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 40;

    public static function getNavigationGroup(): ?string { return '🏢 Properti & Kamar'; }
    public static function getLabel(): ?string { return 'Vendor'; }
    public static function getPluralLabel(): ?string { return 'Vendor Maintenance'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Vendor')->schema([
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')->label('Nama Vendor')->required()->maxLength(255),
                    Forms\Components\Select::make('category')->label('Kategori')->options([
                        'plumbing'   => 'Perpipaan/Air',
                        'electrical' => 'Kelistrikan',
                        'ac'         => 'AC & Pendingin',
                        'cleaning'   => 'Cleaning Service',
                        'carpentry'  => 'Kayu/Furniture',
                        'internet'   => 'Internet/Network',
                        'security'   => 'Keamanan',
                        'general'    => 'Umum',
                    ])->default('general')->required(),
                    Forms\Components\TextInput::make('contact_person')->label('Narahubung'),
                    Forms\Components\TextInput::make('phone')->label('No. HP')->tel(),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('rating')->label('Rating (1-5)')->numeric()->minValue(1)->maxValue(5)->step(0.1),
                ]),
                Forms\Components\Textarea::make('address')->label('Alamat')->rows(2)->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
                TextColumn::make('category')->label('Kategori')->badge()
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'plumbing' => 'Perpipaan', 'electrical' => 'Listrik', 'ac' => 'AC',
                        'cleaning' => 'Cleaning', 'carpentry' => 'Furniture', 'internet' => 'Internet',
                        'security' => 'Keamanan', default => 'Umum',
                    }),
                TextColumn::make('contact_person')->label('Narahubung')->default('-'),
                TextColumn::make('phone')->label('No. HP')->default('-'),
                TextColumn::make('rating')->label('Rating')->placeholder('-')
                    ->formatStateUsing(fn ($state) => $state ? '★ '.number_format((float) $state, 1) : null),
                TextColumn::make('maintenance_requests_count')->counts('maintenanceRequests')->label('Pekerjaan')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->label('Kategori')->options([
                    'plumbing' => 'Perpipaan', 'electrical' => 'Listrik', 'ac' => 'AC',
                    'cleaning' => 'Cleaning', 'general' => 'Umum',
                ]),
                TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
