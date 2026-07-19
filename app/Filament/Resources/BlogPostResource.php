<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model         = BlogPost::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';
    protected static ?int    $navigationSort = 10;

    public static function getNavigationGroup(): ?string { return 'Marketing'; }
    public static function getLabel(): ?string           { return 'Artikel Blog'; }
    public static function getPluralLabel(): ?string     { return 'Artikel Blog'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten')->schema([
                TextInput::make('title')->label('Judul')->required()->columnSpanFull()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
                Textarea::make('excerpt')->label('Ringkasan')->rows(3)->columnSpanFull(),
                RichEditor::make('content')->label('Isi Artikel')->required()->columnSpanFull()
                    ->fileAttachmentsDisk('public')->fileAttachmentsDirectory('blog'),
            ])->columns(2),

            Section::make('SEO & Metadata')->schema([
                TextInput::make('meta_title')->label('Meta Title')->columnSpanFull(),
                Textarea::make('meta_description')->label('Meta Description')->rows(2)->columnSpanFull(),
                FileUpload::make('featured_image')->label('Gambar Unggulan')
                    ->image()->directory('blog/featured')->columnSpanFull(),
            ])->collapsed(),

            Section::make('Publikasi')->schema([
                Grid::make(3)->schema([
                    Select::make('category_id')->label('Kategori')
                        ->options(BlogCategory::pluck('name', 'id'))
                        ->nullable()->placeholder('Tanpa Kategori'),
                    Select::make('author_id')->label('Penulis')
                        ->options(User::pluck('name', 'id'))
                        ->required(),
                    Toggle::make('is_published')->label('Publikasikan')->default(false),
                ]),
                DateTimePicker::make('published_at')->label('Tanggal Publikasi')
                    ->default(now())->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')->label('Gambar')->circular()->width(48)->height(48),
                TextColumn::make('title')->label('Judul')->searchable()->limit(50)->wrap(),
                TextColumn::make('category.name')->label('Kategori')->badge()->color('gray'),
                TextColumn::make('author.name')->label('Penulis'),
                IconColumn::make('is_published')->label('Publish')->boolean(),
                TextColumn::make('published_at')->label('Tanggal')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Kategori')
                    ->options(BlogCategory::pluck('name', 'id')),
                SelectFilter::make('is_published')->label('Status')
                    ->options(['1' => 'Published', '0' => 'Draft']),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
