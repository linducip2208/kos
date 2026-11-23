<?php

namespace App\Filament\Pages;

use App\Models\Property;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getHeading(): string|Htmlable
    {
        $hour = (int) now('Asia/Jakarta')->format('H');
        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 19 => 'Selamat sore',
            default => 'Selamat malam',
        };

        $name = auth()->user()?->name ?? 'Manager';

        return "{$greeting}, {$name}";
    }

    public function getSubheading(): string|Htmlable|null
    {
        $date = now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y');

        return "{$date} • Ringkasan operasional dan keuangan properti Anda dalam satu layar.";
    }

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
