<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositResource\Pages;
use App\Models\Deposit;
use App\Models\Occupant;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 25;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string { return 'Deposit'; }
    public static function getPluralLabel(): ?string { return 'Deposit Jaminan'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Deposit')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('tenant_id')->label('Penyewa')->options(Occupant::pluck('name', 'id'))->required()->searchable(),
                    Forms\Components\Select::make('lease_id')->label('Kontrak Sewa')->relationship('lease', 'id')->nullable()->searchable(),
                    Forms\Components\TextInput::make('amount')->label('Jumlah')->numeric()->prefix('Rp')->required(),
                    Forms\Components\Select::make('type')->label('Jenis')->options([
                        'security' => 'Jaminan Keamanan', 'utility' => 'Jaminan Utilitas', 'key' => 'Kunci', 'other' => 'Lainnya',
                    ])->default('security')->required(),
                    Forms\Components\DatePicker::make('paid_at')->label('Tanggal Bayar'),
                    Forms\Components\Select::make('status')->label('Status')->options([
                        'held' => 'Ditahan', 'refunded_partial' => 'Refund Sebagian', 'refunded_full' => 'Refund Penuh', 'forfeited' => 'Disita',
                    ])->default('held')->required(),
                ]),
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('refunded_amount')->label('Jumlah Refund')->numeric()->prefix('Rp')->nullable(),
                    Forms\Components\DatePicker::make('refunded_at')->label('Tanggal Refund'),
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
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn ($s) => match ($s) { 'security' => 'Jaminan', 'utility' => 'Utilitas', 'key' => 'Kunci', default => $s }),
                TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => match ($s) { 'held' => 'warning', 'refunded_full' => 'success', 'refunded_partial' => 'info', 'forfeited' => 'danger', default => 'gray' }),
                TextColumn::make('paid_at')->label('Dibayar')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['held' => 'Ditahan', 'refunded_full' => 'Refund', 'forfeited' => 'Disita']),
                SelectFilter::make('type')->options(['security' => 'Jaminan', 'utility' => 'Utilitas', 'key' => 'Kunci']),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
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
