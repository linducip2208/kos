<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AuthorizesAccess;
use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use App\Models\Lease;
use App\Models\Occupant;
use App\Services\DepositService;
use App\Support\NavigationGroups;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    use AuthorizesAccess;

    protected static ?string $model = Deposit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroups::FINANCE;
    }

    public static function getLabel(): ?string
    {
        return 'Deposit';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Deposit Jaminan';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Deposit')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('tenant_id')->label('Penyewa')->options(Occupant::pluck('name', 'id'))->required()->searchable(),
                    Forms\Components\Select::make('lease_id')->label('Kontrak Sewa')
                        ->options(fn () => Lease::with('occupant')->get()->mapWithKeys(
                            fn ($l) => [$l->id => ($l->occupant?->name ?? 'Tenant #'.$l->occupant_id).' — '.$l->lease_number]
                        ))->searchable()->nullable(),
                    Forms\Components\TextInput::make('amount')->label('Jumlah')->numeric()->prefix('Rp')->required(),
                    Forms\Components\Select::make('type')->label('Jenis')->options([
                        'security' => 'Jaminan Keamanan', 'utility' => 'Jaminan Utilitas', 'key' => 'Kunci', 'other' => 'Lainnya',
                    ])->default('security')->required(),
                    Forms\Components\DatePicker::make('paid_at')->label('Tanggal Bayar'),
                    Forms\Components\Select::make('status')->label('Status')->options(Deposit::STATUSES)->default('pending')->required(),
                ]),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')->label('Penyewa')->searchable()->sortable(),
                TextColumn::make('lease.lease_number')->label('Kontrak')->default('-'),
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('balance')->label('Saldo')->weight('bold')
                    ->color(fn (Deposit $r) => $r->balance <= 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn (Deposit $r) => 'Rp '.number_format($r->balance, 0, ',', '.')),
                TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn ($s) => match ($s) {
                    'security' => 'Jaminan', 'utility' => 'Utilitas', 'key' => 'Kunci', default => 'Lainnya'
                }),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (Deposit $r) => $r->status_color)
                    ->formatStateUsing(fn ($s) => Deposit::STATUSES[$s] ?? $s),
                TextColumn::make('paid_at')->label('Dibayar')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(Deposit::STATUSES),
                SelectFilter::make('type')->label('Jenis')->options(['security' => 'Jaminan', 'utility' => 'Utilitas', 'key' => 'Kunci']),
            ])
            ->actions([
                Actions\EditAction::make(),

                Actions\ActionGroup::make([
                    Actions\Action::make('mark_received')
                        ->label('Tandai Diterima')->icon('heroicon-o-check-circle')->color('success')
                        ->visible(fn (Deposit $r) => in_array($r->status, ['pending'], true))
                        ->form([
                            Forms\Components\Select::make('method')->label('Metode Bayar')->options(InvoicePayment::METHODS)->default('cash'),
                            Forms\Components\TextInput::make('reference')->label('Referensi'),
                        ])
                        ->action(function (Deposit $record, array $data) {
                            app(DepositService::class)->markReceived($record, $data['method'] ?? 'cash', $data['reference'] ?? null);
                            Notification::make()->title('Deposit diterima & tercatat di ledger.')->success()->send();
                        }),

                    Actions\Action::make('deduct')
                        ->label('Potong Deposit')->icon('heroicon-o-scissors')->color('danger')
                        ->visible(fn (Deposit $r) => ! $r->is_settled && $r->balance > 0)
                        ->form([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('amount')->label('Nominal Potong')->numeric()->prefix('Rp')->required(),
                                Forms\Components\TextInput::make('reason')->label('Alasan')->required()
                                    ->placeholder('Kerusakan, cleaning, tunggakan...'),
                            ]),
                        ])
                        ->action(function (Deposit $record, array $data) {
                            app(DepositService::class)->deduct($record, (float) $data['amount'], $data['reason']);
                            Notification::make()->title('Deposit dipotong. Saldo: Rp '.number_format($record->refresh()->balance, 0, ',', '.'))->warning()->send();
                        }),

                    Actions\Action::make('refund')
                        ->label('Refund Sisa Deposit')->icon('heroicon-o-arrow-uturn-left')->color('info')
                        ->visible(fn (Deposit $r) => ! in_array($r->status, ['refunded', 'forfeited'], true))
                        ->form([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('amount')->label('Nominal Refund')->numeric()->prefix('Rp')
                                    ->default(fn (Deposit $r) => max(0, $r->balance))->required(),
                                Forms\Components\Select::make('method')->label('Metode')->options(InvoicePayment::METHODS)->default('transfer'),
                            ]),
                            Forms\Components\TextInput::make('reason')->label('Alasan')->default('Refund deposit akhir sewa'),
                        ])
                        ->action(function (Deposit $record, array $data) {
                            app(DepositService::class)->refund($record, (float) $data['amount'], $data['method'] ?? 'transfer', $data['reason'] ?? 'Refund deposit');
                            Notification::make()->title('Refund deposit tercatat.')->success()->send();
                        }),

                    Actions\Action::make('forfeit')
                        ->label('Hanguskan')->icon('heroicon-o-fire')->color('danger')
                        ->visible(fn (Deposit $r) => ! in_array($r->status, ['refunded', 'forfeited'], true))
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('reason')->label('Alasan Hangus')->required()->rows(2),
                        ])
                        ->action(function (Deposit $record, array $data) {
                            app(DepositService::class)->forfeit($record, $data['reason']);
                            Notification::make()->title('Deposit dihanguskan.')->warning()->send();
                        }),
                ])->label('Ledger')->icon('heroicon-o-ellipsis-vertical')->color('gray'),

                Actions\Action::make('ledger')
                    ->label('Mutasi')->icon('heroicon-o-receipt-percent')
                    ->url(fn (Deposit $r) => DepositTransactionResource::getUrl('index').'?tableFilters[deposit_id][value]='.$r->id),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeposits::route('/'),
            'create' => Pages\CreateDeposit::route('/create'),
            'edit' => Pages\EditDeposit::route('/{record}/edit'),
        ];
    }
}
