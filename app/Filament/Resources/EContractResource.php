<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EContractResource\Pages;
use App\Models\EContract;
use App\Models\Lease;
use App\Services\EContractService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EContractResource extends Resource
{
    protected static ?string $model         = EContract::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';
    protected static ?int    $navigationSort = 25;

    public static function getNavigationGroup(): ?string { return __('navigation.group_tenant_contract'); }
    public static function getLabel(): ?string           { return 'Kontrak Digital'; }
    public static function getPluralLabel(): ?string     { return 'Kontrak Digital'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Kontrak')->schema([
                Select::make('lease_id')->label('Kontrak Sewa')
                    ->options(
                        Lease::with(['occupant', 'room.property'])->get()
                            ->mapWithKeys(fn ($l) => [$l->id => ($l->occupant->name ?? '-') . ' — ' . ($l->room->property->name ?? '') . ' ' . ($l->room->room_number ?? '')])
                    )->searchable()->required(),
                Textarea::make('content_html')->label('Isi Kontrak (HTML)')->rows(10),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')->label('No. Kontrak')->searchable()->weight('bold'),
                TextColumn::make('lease.occupant.name')->label('Penyewa'),
                TextColumn::make('lease.room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => optional($record->lease?->room?->property)->name . ' - ' . optional($record->lease?->room)->room_number),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray', 'sent' => 'info',
                        'owner_signed' => 'warning', 'fully_signed' => 'success', default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft', 'sent' => 'Terkirim',
                        'owner_signed' => 'TTD Pemilik', 'fully_signed' => 'Ditandatangani Semua', default => 'Kadaluarsa',
                    }),
                TextColumn::make('created_at')->label('Dibuat')->date('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'sent' => 'Terkirim',
                    'owner_signed' => 'TTD Pemilik', 'fully_signed' => 'Ditandatangani Semua',
                ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('generate')
                    ->label('Generate')->icon('heroicon-o-document-plus')->color('info')
                    ->visible(fn (EContract $r) => $r->status === 'draft')
                    ->action(function (EContract $record) {
                        app(EContractService::class)->generate($record->lease);
                        Notification::make()->title('Kontrak berhasil digenerate.')->success()->send();
                    }),
                Actions\Action::make('sign_owner')
                    ->label('Tanda Tangan Pemilik')->icon('heroicon-o-pencil')->color('warning')
                    ->visible(fn (EContract $r) => in_array($r->status, ['draft', 'sent']))
                    ->action(function (EContract $record) {
                        app(EContractService::class)->signByOwner($record, 'owner_signature_placeholder');
                        Notification::make()->title('Kontrak ditandatangani pemilik.')->success()->send();
                    }),
                Actions\Action::make('send_link')
                    ->label('Kirim Link ke Penyewa')->icon('heroicon-o-paper-airplane')->color('success')
                    ->visible(fn (EContract $r) => !empty($r->owner_signature))
                    ->action(function (EContract $record) {
                        app(EContractService::class)->sendSignLink($record);
                        Notification::make()->title('Link tanda tangan terkirim via WhatsApp.')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEContracts::route('/'),
            'create' => Pages\CreateEContract::route('/create'),
            'edit'   => Pages\EditEContract::route('/{record}/edit'),
        ];
    }
}
