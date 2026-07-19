<?php

namespace App\Filament\Resources\EContractResource\Pages;

use App\Filament\Resources\EContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEContract extends EditRecord
{
    protected static string $resource = EContractResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
