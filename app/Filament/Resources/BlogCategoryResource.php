<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\BlogCategoryResource\Pages;
use App\Models\BlogCategory;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogCategoryResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = BlogCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::SETTINGS;
    }

    public static function getLabel(): ?string
    {
        return 'Kategori Blog';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Kategori Blog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->label('Nama Kategori')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('slug')->label('Slug'),
                TextColumn::make('posts_count')->label('Artikel')->counts('posts'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogCategories::route('/'),
            'create' => Pages\CreateBlogCategory::route('/create'),
            'edit' => Pages\EditBlogCategory::route('/{record}/edit'),
        ];
    }
}
