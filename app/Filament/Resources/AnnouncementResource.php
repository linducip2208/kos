<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Models\Property;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 60;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string { return 'Pengumuman'; }
    public static function getPluralLabel(): ?string { return 'Pengumuman Penghuni'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pengumuman')->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(255),
                Forms\Components\RichEditor::make('content')->label('Isi Pengumuman')
                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                    ->required()->columnSpanFull(),
                Grid::make(2)->schema([
                    Forms\Components\Select::make('property_id')->label('Properti (kosongkan = semua)')
                        ->options(Property::pluck('name', 'id'))->searchable()->nullable(),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                    Forms\Components\DateTimePicker::make('published_at')->label('Terbit Mulai')->default(now()),
                    Forms\Components\DateTimePicker::make('expires_at')->label('Berakhir Pada'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->weight('bold'),
                TextColumn::make('property.name')->label('Properti')->placeholder('Semua Properti'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('expires_at')->label('Berakhir')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('createdBy.name')->label('Oleh')->default('-')->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Aktif'),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
