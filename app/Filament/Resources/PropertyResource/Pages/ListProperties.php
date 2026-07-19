<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Properti'),
            Actions\Action::make('export_occupancy')
                ->label('Export Occupancy (Excel)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('print.excel.occupancy'))
                ->openUrlInNewTab(),
        ];
    }
}
