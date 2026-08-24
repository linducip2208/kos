<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CrmActivityResource\Pages;
use App\Models\BookingRequest;
use App\Models\CrmActivity;
use App\Models\User;
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

class CrmActivityResource extends Resource
{
    protected static ?string $model = CrmActivity::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?int $navigationSort = 55;

    public static function getNavigationGroup(): ?string { return '👤 Penghuni & Sewa'; }
    public static function getLabel(): ?string { return 'Aktivitas CRM'; }
    public static function getPluralLabel(): ?string { return 'Aktivitas CRM'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Aktivitas Follow-up')->schema([
                Grid::make(2)->schema([
                    Forms\Components\Select::make('booking_request_id')->label('Prospek (Booking)')
                        ->options(BookingRequest::query()->get()->mapWithKeys(
                            fn ($b) => [$b->id => ($b->name ?? 'Lead #'.$b->id)]
                        ))->searchable()->required(),
                    Forms\Components\Select::make('user_id')->label('Petugas')
                        ->options(User::pluck('name', 'id'))->default(auth()->id())->searchable(),
                    Forms\Components\Select::make('type')->label('Jenis')
                        ->options(CrmActivity::TYPES)->default('note')->required(),
                    Forms\Components\TextInput::make('subject')->label('Subjek')->required()->maxLength(255),
                    Forms\Components\DateTimePicker::make('next_follow_up_at')->label('Follow-up Berikutnya'),
                ]),
                Forms\Components\RichEditor::make('description')->label('Deskripsi')->columnSpanFull()
                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('bookingRequest.name')->label('Prospek')->searchable()->default('-'),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->color(fn ($s) => match ($s) {
                        'call' => 'info', 'whatsapp' => 'success', 'email' => 'warning',
                        'viewing' => 'primary', 'stage_change' => 'gray', default => 'secondary',
                    })
                    ->formatStateUsing(fn ($s) => CrmActivity::TYPES[$s] ?? $s),
                TextColumn::make('subject')->label('Subjek')->limit(40)->searchable(),
                TextColumn::make('next_follow_up_at')->label('Follow-up')->dateTime('d M Y H:i')
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null)
                    ->placeholder('-'),
                TextColumn::make('user.name')->label('Petugas')->default('-'),
            ])
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(CrmActivity::TYPES),
                SelectFilter::make('user_id')->label('Petugas')->options(fn () => User::pluck('name', 'id'))->searchable(),
            ])
            ->actions([Actions\EditAction::make(), Actions\DeleteAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCrmActivities::route('/'),
            'create' => Pages\CreateCrmActivity::route('/create'),
            'edit' => Pages\EditCrmActivity::route('/{record}/edit'),
        ];
    }
}
