<?php

namespace App\Filament\Resources\EContractResource\Pages;

use App\Filament\Resources\EContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEContracts extends ListRecords
{
    protected static string $resource = EContractResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
