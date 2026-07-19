<?php

namespace App\Filament\Resources\OccupantResource\Pages;

use App\Filament\Resources\OccupantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOccupants extends ListRecords
{
    protected static string $resource = OccupantResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
