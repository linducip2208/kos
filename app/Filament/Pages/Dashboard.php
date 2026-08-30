<?php

namespace App\Filament\Pages;

use App\Models\Property;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('property_id')->label('Properti')->options(fn () => Property::query()->where('is_active', true)->pluck('name', 'id'))->searchable()->placeholder('Semua properti'),
            Select::make('period')->label('Periode')->options([
                'today' => 'Hari ini', '7d' => '7 hari', 'month' => 'Bulan ini', 'last_month' => 'Bulan lalu', 'year' => 'Tahun ini', 'custom' => 'Custom',
            ])->default('month')->live(),
            DatePicker::make('from')->label('Dari')->visible(fn ($get) => $get('period') === 'custom'),
            DatePicker::make('to')->label('Sampai')->visible(fn ($get) => $get('period') === 'custom'),
        ]);
    }
}
