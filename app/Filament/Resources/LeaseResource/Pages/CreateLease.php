<?php

namespace App\Filament\Resources\LeaseResource\Pages;

use App\Filament\Resources\LeaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateLease extends CreateRecord
{
    protected static string $resource = LeaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $prefix = setting('lease_prefix', 'KTR');
        $count  = \App\Models\Lease::whereYear('created_at', now()->year)->count() + 1;
        $data['lease_number'] = sprintf('%s-%s-%04d', $prefix, now()->format('Ym'), $count);
        $data['created_by']   = auth()->id();
        return $data;
    }
}
