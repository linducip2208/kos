<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $prefix = setting('invoice_prefix', 'INV');
        $count  = \App\Models\Invoice::whereYear('created_at', now()->year)->count() + 1;
        $data['invoice_number'] = sprintf('%s-%s-%04d', $prefix, now()->format('Ym'), $count);
        return $data;
    }
}
