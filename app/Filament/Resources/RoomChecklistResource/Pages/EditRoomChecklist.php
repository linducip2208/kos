<?php

namespace App\Filament\Resources\RoomChecklistResource\Pages;

use App\Filament\Resources\RoomChecklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomChecklist extends EditRecord
{
    protected static string $resource = RoomChecklistResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
