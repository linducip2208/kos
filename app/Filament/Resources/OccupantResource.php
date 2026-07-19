<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OccupantResource\Pages;
use App\Models\Occupant;
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
use Filament\Tables\Table;

class OccupantResource extends Resource
{
    protected static ?string $model          = Occupant::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-users';
    protected static ?int    $navigationSort = 10;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string            { return 'Penyewa'; }
    public static function getPluralLabel(): ?string      { return 'Penyewa'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pribadi')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama Lengkap')->required(),
                    TextInput::make('phone')->label('No. Telepon')->tel()->required(),
                    TextInput::make('email')->label('Email')->email(),
                    TextInput::make('whatsapp')->label('No. WhatsApp')->tel()
                        ->placeholder('628xxxxxxxxxx')
                        ->helperText('Kosongkan jika sama dengan no. telepon'),
                ]),
            ]),

            Section::make('Identitas')->schema([
                Grid::make(2)->schema([
                    Select::make('id_type')->label('Jenis ID')
                        ->options(['ktp' => 'KTP', 'sim' => 'SIM', 'passport' => 'Paspor', 'kitas' => 'KITAS'])
                        ->default('ktp'),
                    TextInput::make('id_number')->label('No. Identitas'),
                    TextInput::make('occupation')->label('Pekerjaan'),
                    TextInput::make('workplace')->label('Tempat Kerja / Institusi'),
                ]),
                Textarea::make('address')->label('Alamat Asal')->rows(2),
            ]),

            Section::make('Kontak Darurat')->schema([
                Grid::make(3)->schema([
                    TextInput::make('emergency_contact.name')->label('Nama Kontak Darurat'),
                    TextInput::make('emergency_contact.phone')->label('No. Telepon')->tel(),
                    TextInput::make('emergency_contact.relation')->label('Hubungan'),
                ]),
            ]),

            Section::make('Catatan')->schema([
                Textarea::make('notes')->label('Catatan')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable()->weight('bold'),
                TextColumn::make('phone')->label('Telepon')->searchable(),
                TextColumn::make('whatsapp')->label('WhatsApp')
                    ->formatStateUsing(fn ($state, Occupant $record) => $state ?? $record->phone),
                TextColumn::make('occupation')->label('Pekerjaan'),
                TextColumn::make('activeLease.room.room_number')->label('Kamar Aktif')
                    ->default('-')->badge()->color('success'),
                TextColumn::make('created_at')->label('Terdaftar')->date('d/m/Y')->sortable(),
            ])
            ->filters([])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('leases')
                    ->label('Kontrak')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Occupant $r) => LeaseResource::getUrl('index') . '?tableFilters[occupant_id][value]=' . $r->id),
                Actions\Action::make('portal_access')
                    ->label('Set Password Portal')->icon('heroicon-o-key')->color('info')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('portal_password')
                            ->label('Password Baru')->required()->minLength(6)->password()->revealable(),
                        \Filament\Forms\Components\Toggle::make('portal_active')->label('Aktifkan Akses Portal')->default(true),
                    ])
                    ->action(function (Occupant $record, array $data) {
                        $record->update([
                            'portal_password' => \Illuminate\Support\Facades\Hash::make($data['portal_password']),
                            'portal_active'   => $data['portal_active'],
                        ]);
                        \Filament\Notifications\Notification::make()->title('Akses portal diperbarui.')->success()->send();
                    }),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOccupants::route('/'),
            'create' => Pages\CreateOccupant::route('/create'),
            'edit'   => Pages\EditOccupant::route('/{record}/edit'),
        ];
    }
}
