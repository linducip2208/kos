<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingRequestResource\Pages;
use App\Models\BookingRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingRequestResource extends Resource
{
    protected static ?string $model          = BookingRequest::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?int    $navigationSort = 30;

    public static function getNavigationGroup(): ?string { return __('navigation.group_tenant_contract'); }
    public static function getLabel(): ?string           { return 'Permintaan Booking'; }
    public static function getPluralLabel(): ?string     { return 'Booking Online'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Calon Penyewa')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Nama Lengkap')->required(),
                    TextInput::make('phone')->label('No. HP')->required(),
                    TextInput::make('email')->label('Email')->email(),
                    TextInput::make('whatsapp')->label('WhatsApp'),
                ]),
            ]),
            Section::make('Preferensi Kamar')->schema([
                Grid::make(3)->schema([
                    Select::make('property_id')->label('Properti')->options(Property::pluck('name', 'id'))->required(),
                    Select::make('room_type_id')->label('Tipe Kamar')->options(RoomType::pluck('name', 'id'))->nullable(),
                    Select::make('room_id')->label('Kamar Spesifik')->options(
                        Room::where('status', 'available')->with('property')
                            ->get()->mapWithKeys(fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number])
                    )->nullable(),
                ]),
                Grid::make(2)->schema([
                    DatePicker::make('desired_move_in')->label('Rencana Masuk')->required(),
                    Select::make('billing_cycle')->label('Siklus Sewa')
                        ->options([
                            'daily'     => 'Harian',
                            'weekly'    => 'Mingguan',
                            'monthly'   => 'Bulanan',
                            'quarterly' => 'Triwulan',
                            'yearly'    => 'Tahunan',
                        ])
                        ->default('monthly'),
                ]),
                Textarea::make('message')->label('Pesan/Pertanyaan')->rows(3),
            ]),
            Section::make('Status Admin')->schema([
                Grid::make(2)->schema([
                    Select::make('status')->options([
                        'pending' => 'Menunggu', 'contacted' => 'Sudah Dihubungi',
                        'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'converted' => 'Jadi Penyewa',
                    ])->default('pending')->required(),
                ]),
                Textarea::make('admin_notes')->label('Catatan Admin')->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
                TextColumn::make('phone')->label('HP'),
                TextColumn::make('property.name')->label('Properti'),
                TextColumn::make('desired_move_in')->label('Rencana Masuk')->date('d/m/Y'),
                TextColumn::make('billing_cycle')->label('Siklus')
                    ->formatStateUsing(fn ($s) => match ($s) { 'monthly' => 'Bulanan', 'quarterly' => 'Triwulan', default => 'Tahunan' }),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($s) => match ($s) {
                        'pending' => 'warning', 'contacted' => 'info',
                        'approved' => 'success', 'rejected' => 'danger', default => 'gray',
                    })
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'pending' => 'Menunggu', 'contacted' => 'Dihubungi',
                        'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Jadi Penyewa',
                    }),
                TextColumn::make('created_at')->label('Diterima')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Menunggu', 'contacted' => 'Dihubungi',
                    'approved' => 'Disetujui', 'rejected' => 'Ditolak',
                ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('contact')
                    ->label('Hubungi WA')->icon('heroicon-o-chat-bubble-left')->color('success')
                    ->url(fn (BookingRequest $r) => 'https://wa.me/' . preg_replace('/\D/', '', $r->whatsapp ?? $r->phone))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookingRequests::route('/'),
            'create' => Pages\CreateBookingRequest::route('/create'),
            'edit'   => Pages\EditBookingRequest::route('/{record}/edit'),
        ];
    }
}
