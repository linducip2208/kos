<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Tambah Kamar')];
    }

    public function getTabs(): array
    {
        return [
            'all'         => Tab::make('Semua'),
            'available'   => Tab::make('Tersedia')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'available')),
            'occupied'    => Tab::make('Terisi')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'occupied')),
            'maintenance' => Tab::make('Maintenance')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'maintenance')),
        ];
    }
}
