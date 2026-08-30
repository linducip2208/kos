<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Property;
use App\Models\Testimonial;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = Testimonial::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 61;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::SETTINGS;
    }

    public static function getLabel(): ?string
    {
        return 'Testimoni';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Testimoni Penyewa';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    Select::make('property_id')
                        ->label('Properti')
                        ->options(Property::pluck('name', 'id'))
                        ->nullable()
                        ->placeholder('Semua Properti'),
                    TextInput::make('order')->label('Urutan')->numeric()->default(0),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama Penyewa')->required(),
                    TextInput::make('occupation')->label('Pekerjaan')->placeholder('Mahasiswa, Karyawan, dll'),
                ]),
                Grid::make(2)->schema([
                    Select::make('rating')->label('Rating')
                        ->options([1 => '⭐ 1', 2 => '⭐⭐ 2', 3 => '⭐⭐⭐ 3', 4 => '⭐⭐⭐⭐ 4', 5 => '⭐⭐⭐⭐⭐ 5'])
                        ->default(5)->required(),
                    Toggle::make('is_active')->label('Tampilkan')->default(true),
                ]),
                FileUpload::make('avatar')->label('Foto (opsional)')->image()->directory('testimonials')->imagePreviewHeight('80')->columnSpanFull(),
                Textarea::make('content')->label('Isi Testimoni')->rows(4)->required()->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->label('#')->sortable()->width(50),
                ImageColumn::make('avatar')->label('Foto')->circular()->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=3b82f6&color=fff'),
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
                TextColumn::make('occupation')->label('Pekerjaan')->color('gray'),
                TextColumn::make('rating')->label('Rating')
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)),
                TextColumn::make('content')->label('Testimoni')->limit(60)->color('gray'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('property_id')->label('Properti')->options(Property::pluck('name', 'id')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->reorderable('order')
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
