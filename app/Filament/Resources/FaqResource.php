<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use App\Models\Property;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model         = Faq::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?int    $navigationSort = 60;

    public static function getNavigationGroup(): ?string { return 'Konten Landing'; }
    public static function getLabel(): ?string           { return 'FAQ'; }
    public static function getPluralLabel(): ?string     { return 'FAQ (Pertanyaan Umum)'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    Select::make('property_id')
                        ->label('Properti (kosongkan = tampil semua)')
                        ->options(Property::pluck('name', 'id'))
                        ->nullable()
                        ->placeholder('Semua Properti'),
                    TextInput::make('order')->label('Urutan')->numeric()->default(0),
                ]),
                TextInput::make('question')->label('Pertanyaan')->required()->columnSpanFull(),
                Textarea::make('answer')->label('Jawaban')->rows(4)->required()->columnSpanFull(),
                Toggle::make('is_active')->label('Tampilkan di Landing Page')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->label('#')->sortable()->width(50),
                TextColumn::make('property.name')->label('Properti')->default('Semua')->badge()->color('gray'),
                TextColumn::make('question')->label('Pertanyaan')->searchable()->limit(60)->wrap(),
                TextColumn::make('answer')->label('Jawaban')->limit(80)->color('gray'),
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
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
