<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 80;

    public static function getNavigationGroup(): ?string { return '⚙️ Sistem'; }
    public static function getLabel(): ?string { return 'Pengguna'; }
    public static function getPluralLabel(): ?string { return 'Manajemen Pengguna'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pengguna')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama')->required(),
                    TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                    TextInput::make('phone')->label('Telepon')->nullable(),
                    Select::make('role')->label('Role')->options([
                        'owner' => 'Owner', 'staff' => 'Staff', 'viewer' => 'Viewer',
                    ])->required(),
                ]),
                TextInput::make('password')->label('Password')->password()
                    ->dehydrateStateUsing(fn ($s) => $s ? Hash::make($s) : null)
                    ->dehydrated(fn ($s) => filled($s))
                    ->nullable(),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('role')->label('Role')->badge()
                    ->color(fn ($s) => match ($s) { 'owner' => 'success', 'staff' => 'primary', 'viewer' => 'gray', default => 'gray' }),
                TextColumn::make('phone')->label('Telepon'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('created_at')->label('Dibuat')->date('d M Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
