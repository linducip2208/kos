<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaseResource\Pages;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Room;
use App\Services\CheckInOutService;
use App\Services\LeaseWorkflowService;
use App\Services\MoveRoomService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LeaseResource extends Resource
{
    protected static ?string $model           = Lease::class;
    protected static string|\BackedEnum|null  $navigationIcon  = 'heroicon-o-document-text';
    protected static ?int    $navigationSort  = 20;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string            { return __('navigation.lease'); }
    public static function getPluralLabel(): ?string      { return __('navigation.leases'); }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kamar & Penyewa')->schema([
                Grid::make(2)->schema([
                    Select::make('room_id')
                        ->label('Kamar')
                        ->options(
                            Room::with('property')
                                ->whereIn('status', ['available', 'reserved'])
                                ->get()
                                ->mapWithKeys(fn ($r) => [$r->id => $r->property->name . ' - ' . $r->room_number . ($r->name ? " ({$r->name})" : '')])
                        )
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            if (!$state) return;
                            $room = Room::find($state);
                            if ($room) $set('price', $room->effective_price_monthly);
                        }),

                    Select::make('occupant_id')
                        ->label('Penyewa')
                        ->options(Occupant::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->createOptionForm([
                            Grid::make(2)->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('phone')->required(),
                                TextInput::make('email'),
                                TextInput::make('whatsapp'),
                            ]),
                        ])
                        ->createOptionUsing(fn (array $data) => Occupant::create($data)->id),
                ]),
            ]),

            Section::make('Periode & Harga')->schema([
                Grid::make(3)->schema([
                    DatePicker::make('start_date')->label('Tanggal Masuk')->required(),
                    DatePicker::make('end_date')->label('Tanggal Keluar')->required(),
                    Select::make('billing_cycle')->label('Siklus Tagihan')
                        ->options([
                            'daily'     => 'Harian',
                            'weekly'    => 'Mingguan',
                            'monthly'   => 'Bulanan',
                            'quarterly' => 'Triwulan',
                            'yearly'    => 'Tahunan',
                        ])
                        ->default('monthly')->required(),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('price')->label('Harga Sewa')->numeric()->prefix('Rp')->required(),
                    TextInput::make('deposit')->label('Deposit')->numeric()->prefix('Rp')->default(0),
                    TextInput::make('billing_date')->label('Tanggal Tagih (tgl)')->numeric()->minValue(1)->maxValue(28)->default(1),
                ]),
            ]),

            Section::make('Status')->schema([
                Grid::make(2)->schema([
                    Select::make('status')
                        ->options(Lease::STATUSES)
                        ->default('draft')->required(),
                    Textarea::make('termination_reason')->label('Alasan Terminasi/Berakhir')
                        ->visible(fn (Get $get) => in_array($get('status'), ['terminated', 'ended'])),
                ]),
                Textarea::make('notes')->label('Catatan')->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lease_number')->label('No. Kontrak')->searchable()->weight('bold'),
                TextColumn::make('occupant.name')->label('Penyewa')->searchable()->sortable(),
                TextColumn::make('room.room_number')->label('Kamar')
                    ->formatStateUsing(fn ($record) => $record->room ? $record->room->property->name . ' - ' . $record->room->room_number : '-'),
                TextColumn::make('start_date')->label('Masuk')->date('d/m/Y')->sortable(),
                TextColumn::make('end_date')->label('Keluar')->date('d/m/Y')->sortable()
                    ->color(fn (Lease $record) => $record->is_expiring_soon ? 'warning' : null),
                TextColumn::make('price')->label('Harga')->money('IDR'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success', 'expiring_soon' => 'warning',
                        'awaiting_signature' => 'info', 'pending_approval' => 'gray',
                        'renewed' => 'primary',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => Lease::statusLabel($state)),
            ])
            ->filters([
                SelectFilter::make('occupant_id')->label('Penyewa')
                    ->options(fn () => Occupant::pluck('name', 'id'))->searchable(),
                SelectFilter::make('status')->label('Status')->options(Lease::STATUSES),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('invoices')
                    ->label('Tagihan')
                    ->icon('heroicon-o-banknotes')
                    ->url(fn (Lease $r) => InvoiceResource::getUrl('index') . '?tableFilters[lease_id][value]=' . $r->id),

                Actions\ActionGroup::make([
                    Actions\Action::make('wf_submit')
                        ->label('Ajukan Persetujuan')->icon('heroicon-o-paper-airplane')->color('gray')
                        ->visible(fn (Lease $r) => $r->status === 'draft')
                        ->requiresConfirmation()
                        ->action(function (Lease $record) {
                            app(LeaseWorkflowService::class)->submitForApproval($record);
                            Notification::make()->title('Kontrak diajukan untuk persetujuan.')->success()->send();
                        }),
                    Actions\Action::make('wf_approve')
                        ->label('Setujui Kontrak')->icon('heroicon-o-hand-thumb-up')->color('success')
                        ->visible(fn (Lease $r) => in_array($r->status, ['draft', 'pending_approval']))
                        ->requiresConfirmation()
                        ->action(function (Lease $record) {
                            app(LeaseWorkflowService::class)->approve($record, auth()->user());
                            Notification::make()->title('Kontrak disetujui, menunggu TTD tenant.')->success()->send();
                        }),
                    Actions\Action::make('wf_activate')
                        ->label('Aktifkan Kontrak')->icon('heroicon-o-play-circle')->color('success')
                        ->visible(fn (Lease $r) => in_array($r->status, ['draft', 'awaiting_signature']))
                        ->requiresConfirmation()
                        ->modalDescription('Kontrak menjadi aktif dan kamar berstatus terisi.')
                        ->action(function (Lease $record) {
                            app(LeaseWorkflowService::class)->activate($record);
                            Notification::make()->title('Kontrak aktif.')->success()->send();
                        }),
                    Actions\Action::make('wf_renew')
                        ->label('Perpanjang Kontrak')->icon('heroicon-o-arrow-path')->color('primary')
                        ->visible(fn (Lease $r) => in_array($r->status, ['active', 'expiring_soon']))
                        ->form([
                            Grid::make(2)->schema([
                                DatePicker::make('start_date')->label('Mulai')->default(fn (Lease $r) => $r->end_date?->copy()->addDay())->required(),
                                DatePicker::make('end_date')->label('Selesai')->required(),
                                TextInput::make('price')->label('Harga Baru (opsional)')->numeric()->prefix('Rp'),
                                Select::make('billing_cycle')->label('Siklus')->options([
                                    'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan',
                                    'quarterly' => 'Triwulan', 'yearly' => 'Tahunan',
                                ])->default(fn (Lease $r) => $r->billing_cycle),
                            ]),
                        ])
                        ->action(function (Lease $record, array $data) {
                            $new = app(LeaseWorkflowService::class)->renew($record, array_filter([
                                'start_date'    => $data['start_date'] ?? null,
                                'end_date'      => $data['end_date'] ?? null,
                                'price'         => isset($data['price']) && $data['price'] !== '' ? (float) $data['price'] : null,
                                'billing_cycle' => $data['billing_cycle'] ?? null,
                            ]), auth()->user());
                            Notification::make()->title("Kontrak diperpanjang — kontrak baru {$new->lease_number} dibuat.")->success()->send();
                        }),
                    Actions\Action::make('wf_notice')
                        ->label('Terima Notice Keluar')->icon('heroicon-o-flag')->color('warning')
                        ->visible(fn (Lease $r) => $r->isOperational())
                        ->form([
                            DatePicker::make('effective_date')->label('Tanggal Keluar Efektif')->default(fn (Lease $r) => $r->end_date),
                        ])
                        ->action(function (Lease $record, array $data) {
                            app(LeaseWorkflowService::class)->giveNotice($record, isset($data['effective_date']) ? \Carbon\Carbon::parse($data['effective_date']) : null);
                            Notification::make()->title('Notice keluar dicatat.')->success()->send();
                        }),
                    Actions\Action::make('wf_terminate')
                        ->label('Terminasi Dini')->icon('heroicon-o-x-circle')->color('danger')
                        ->visible(fn (Lease $r) => !in_array($r->status, ['renewed', 'ended', 'terminated', 'cancelled'], true))
                        ->form([
                            Textarea::make('reason')->label('Alasan Terminasi')->required()->rows(3),
                        ])
                        ->action(function (Lease $record, array $data) {
                            app(LeaseWorkflowService::class)->terminate($record, $data['reason']);
                            Notification::make()->title('Kontrak diterminasi.')->warning()->send();
                        }),

                    Actions\Action::make('move_room')
                        ->label('Pindah Kamar')->icon('heroicon-o-arrows-right-left')->color('info')
                        ->visible(fn (Lease $r) => $r->isOperational())
                        ->form([
                            Select::make('to_room_id')->label('Kamar Tujuan')
                                ->options(function (Lease $r) {
                                    $exclude = $r->room_id;

                                    return Room::with('property')
                                        ->where('is_active', true)
                                        ->whereNotIn('status', ['occupied', 'reserved', 'inactive'])
                                        ->when($exclude, fn ($q) => $q->whereKeyNot($exclude))
                                        ->get()
                                        ->mapWithKeys(fn ($room) => [$room->id => $room->property->name.' - '.$room->room_number.' (Rp '.number_format((float) ($room->effective_price_monthly ?: $room->roomType?->base_price_monthly ?? 0), 0, ',', '.').')']);
                                })
                                ->searchable()->required()->live(),
                            DatePicker::make('effective_date')->label('Tanggal Efektif')->default(today()),
                            TextInput::make('prorate_amount')->label('Selisih Prorate (Rp)')
                                ->numeric()->prefix('Rp')
                                ->hint('Kosongkan = hitung otomatis'),
                            Toggle::make('transfer_deposit')->label('Transfer deposit ke kamar baru')->default(true),
                            Textarea::make('notes')->label('Catatan')->rows(2),
                        ])
                        ->modalDescription('Riwayat sewa tetap utuh di kontrak yang sama; selisih harga dibuatkan invoice penyesuaian.')
                        ->action(function (Lease $record, array $data) {
                            $toRoom = Room::findOrFail($data['to_room_id']);
                            $transfer = app(MoveRoomService::class)->transfer($record, $toRoom, [
                                'effective_date'   => $data['effective_date'] ?? null,
                                'prorate_amount'   => filled($data['prorate_amount'] ?? null) ? (float) $data['prorate_amount'] : null,
                                'transfer_deposit' => (bool) ($data['transfer_deposit'] ?? true),
                                'notes'            => $data['notes'] ?? null,
                            ]);
                            Notification::make()->title('Tenant dipindah ke kamar baru. Prorate: '.$transfer->prorate_label)->success()->send();
                        }),

                    Actions\Action::make('checkin')
                        ->label('Proses Check-in')->icon('heroicon-o-key')->color('success')
                        ->visible(fn (Lease $r) => $r->isOperational() && !$r->checkinRecords()->where('type', 'check_in')->exists())
                        ->form([
                            TextInput::make('meter_electric')->label('Meteran Listrik Awal')->numeric(),
                            TextInput::make('meter_water')->label('Meteran Air Awal')->numeric(),
                            Toggle::make('key_handover')->label('Serah Terima Kunci')->default(true),
                            TextInput::make('acknowledged_by')->label('Diakui Oleh (Tenant)')
                                ->default(fn (Lease $r) => $r->occupant?->name),
                        ])
                        ->action(function (Lease $record, array $data) {
                            app(CheckInOutService::class)->checkIn($record, [
                                'meter_electric'  => filled($data['meter_electric'] ?? null) ? (float) $data['meter_electric'] : null,
                                'meter_water'     => filled($data['meter_water'] ?? null) ? (float) $data['meter_water'] : null,
                                'key_handover'    => (bool) ($data['key_handover'] ?? true),
                                'acknowledged_by' => $data['acknowledged_by'] ?? null,
                            ]);
                            Notification::make()->title('Check-in tercatat, kamar berstatus terisi.')->success()->send();
                        }),
                    Actions\Action::make('checkout')
                        ->label('Proses Check-out')->icon('heroicon-o-arrow-right-on-rectangle')->color('danger')
                        ->visible(fn (Lease $r) => $r->isOperational() && $r->checkinRecords()->where('type', 'check_in')->exists())
                        ->form([
                            TextInput::make('meter_electric')->label('Meteran Listrik Akhir')->numeric(),
                            TextInput::make('meter_water')->label('Meteran Air Akhir')->numeric(),
                            TextInput::make('damage_amount')->label('Biaya Kerusakan (Rp)')->numeric()->prefix('Rp')->default(0),
                            TextInput::make('cleaning_amount')->label('Biaya Cleaning (Rp)')->numeric()->prefix('Rp')->default(0),
                            Toggle::make('execute_settlement')->label('Eksekusi Settlement Deposit Otomatis')->default(true)
                                ->helperText('Deposit dipotong sesuai tunggakan, kerusakan & cleaning; sisa direfund.'),
                        ])
                        ->action(function (Lease $record, array $data) {
                            $out = app(CheckInOutService::class)->checkOut($record, [
                                'meter_electric'     => filled($data['meter_electric'] ?? null) ? (float) $data['meter_electric'] : null,
                                'meter_water'        => filled($data['meter_water'] ?? null) ? (float) $data['meter_water'] : null,
                                'damage_amount'      => (float) ($data['damage_amount'] ?? 0),
                                'cleaning_amount'    => (float) ($data['cleaning_amount'] ?? 0),
                                'execute_settlement' => (bool) ($data['execute_settlement'] ?? true),
                            ]);
                            Notification::make()
                                ->title('Check-out selesai.')
                                ->body(isset($out->tenant_payable) && (float) $out->tenant_payable > 0
                                    ? 'Tenant masih menanggung Rp '.number_format((float) $out->tenant_payable, 0, ',', '.').'.'
                                    : 'Settlement deposit telah dieksekusi.')
                                ->success()->send();
                        }),
                ])
                    ->label('Aksi')->icon('heroicon-o-ellipsis-vertical')->color('gray'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeases::route('/'),
            'create' => Pages\CreateLease::route('/create'),
            'edit'   => Pages\EditLease::route('/{record}/edit'),
        ];
    }
}
