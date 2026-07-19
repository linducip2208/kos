<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageTemplateResource\Pages;
use App\Models\MessageTemplate;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessageTemplateResource extends Resource
{
    protected static ?string $model = MessageTemplate::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string { return '🔌 Integrasi'; }
    public static function getLabel(): ?string { return 'Template Pesan'; }
    public static function getPluralLabel(): ?string { return 'Template Pesan'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Nama Template')->required(),
                Forms\Components\Select::make('type')->label('Jenis')->options([
                    'invoice' => 'Tagihan', 'reminder' => 'Pengingat', 'overdue' => 'Jatuh Tempo',
                    'welcome' => 'Selamat Datang', 'lease_renewal' => 'Perpanjangan Kontrak', 'custom' => 'Kustom',
                ])->required(),
                Forms\Components\Textarea::make('message')->label('Pesan')->rows(6)->required()->columnSpanFull()
                    ->helperText('Gunakan {nama}, {kamar}, {total}, {tanggal} sebagai placeholder'),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Template')->searchable()->sortable(),
                TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn ($s) => match ($s) { 'invoice' => 'Tagihan', 'reminder' => 'Pengingat', 'overdue' => 'Jatuh Tempo', 'welcome' => 'Welcome', 'lease_renewal' => 'Kontrak', default => $s }),
                TextColumn::make('message')->label('Pesan')->limit(50),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMessageTemplates::route('/'),
            'create' => Pages\CreateMessageTemplate::route('/create'),
            'edit' => Pages\EditMessageTemplate::route('/{record}/edit'),
        ];
    }
}
