<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\Property;
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

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?int $navigationSort = 30;

    public static function getNavigationGroup(): ?string { return '💰 Keuangan'; }
    public static function getLabel(): ?string { return 'Pengeluaran'; }
    public static function getPluralLabel(): ?string { return 'Pengeluaran'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Pengeluaran')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('property_id')->label('Properti')->options(Property::pluck('name', 'id'))->nullable(),
                    Forms\Components\Select::make('category')->label('Kategori')->options([
                        'maintenance' => 'Perbaikan', 'utility' => 'Utilitas', 'salary' => 'Gaji',
                        'cleaning' => 'Kebersihan', 'supplies' => 'Perlengkapan', 'other' => 'Lainnya',
                    ])->required(),
                    Forms\Components\TextInput::make('amount')->label('Jumlah')->numeric()->prefix('Rp')->required(),
                    Forms\Components\DatePicker::make('expense_date')->label('Tanggal')->required()->default(now()),
                ]),
                Grid::make(2)->schema([
                    Forms\Components\TextInput::make('vendor')->label('Vendor/Penyedia'),
                    Forms\Components\FileUpload::make('receipt_path')->label('Bukti Pembayaran')->directory('expenses'),
                ]),
                Forms\Components\TextInput::make('description')->label('Keterangan')->required()->columnSpanFull(),
                Forms\Components\Textarea::make('notes')->label('Catatan')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')->label('Tgl')->date('d M Y')->sortable(),
                TextColumn::make('category')->label('Kategori')->badge()->formatStateUsing(fn ($s) => match ($s) { 'maintenance' => 'Perbaikan', 'utility' => 'Utilitas', 'salary' => 'Gaji', 'cleaning' => 'Kebersihan', 'supplies' => 'Perlengkapan', default => $s }),
                TextColumn::make('description')->label('Keterangan')->searchable()->limit(40),
                TextColumn::make('amount')->label('Jumlah')->money('IDR')->sortable(),
                TextColumn::make('property.name')->label('Properti')->default('-'),
            ])
            ->filters([
                SelectFilter::make('category')->options(['maintenance' => 'Perbaikan', 'utility' => 'Utilitas', 'salary' => 'Gaji', 'cleaning' => 'Kebersihan', 'supplies' => 'Perlengkapan', 'other' => 'Lainnya']),
                SelectFilter::make('property_id')->label('Properti')->options(Property::pluck('name', 'id')),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
