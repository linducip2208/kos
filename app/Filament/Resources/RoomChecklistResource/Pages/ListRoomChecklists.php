<?php

namespace App\Filament\Resources\RoomChecklistResource\Pages;

use App\Filament\Resources\RoomChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomChecklists extends ListRecords
{
    protected static string $resource = RoomChecklistResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
