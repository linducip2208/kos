<?php

namespace App\Filament\Resources\UtilityReadingResource\Pages;

use App\Filament\Resources\UtilityReadingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUtilityReading extends EditRecord
{
    protected static string $resource = UtilityReadingResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
