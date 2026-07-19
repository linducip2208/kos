<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use App\Models\Property;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model         = ContactSubmission::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static ?int    $navigationSort = 62;

    public static function getNavigationGroup(): ?string { return 'Konten Landing'; }
    public static function getLabel(): ?string           { return 'Pesan Masuk'; }
    public static function getPluralLabel(): ?string     { return 'Pesan Masuk (Kontak)'; }

    public static function getNavigationBadge(): ?string
    {
        return (string) ContactSubmission::where('status', 'new')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pesan dari Pengunjung')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama')->disabled(),
                    TextInput::make('phone')->label('No. HP')->disabled(),
                    TextInput::make('email')->label('Email')->disabled(),
                    TextInput::make('subject')->label('Subjek')->disabled(),
                ]),
                Textarea::make('message')->label('Pesan')->rows(4)->disabled()->columnSpanFull(),
            ]),
            Section::make('Balasan Admin')->schema([
                Select::make('status')->label('Status')
                    ->options(['new' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dibalas'])
                    ->required(),
                Textarea::make('reply')->label('Balasan (untuk catatan internal)')->rows(4)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('property.name')->label('Properti')->default('-')->badge()->color('gray'),
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
                TextColumn::make('phone')->label('No. HP'),
                TextColumn::make('subject')->label('Subjek')->limit(40)->default('-'),
                TextColumn::make('message')->label('Pesan')->limit(60)->color('gray'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'new'     => 'warning',
                        'read'    => 'info',
                        'replied' => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'new' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dibalas', default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options(['new' => 'Baru', 'read' => 'Dibaca', 'replied' => 'Dibalas']),
                SelectFilter::make('property_id')->label('Properti')->options(Property::pluck('name', 'id')),
            ])
            ->actions([
                Actions\EditAction::make()->label('Balas/Update'),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'edit'  => Pages\EditContactSubmission::route('/{record}/edit'),
        ];
    }
}
