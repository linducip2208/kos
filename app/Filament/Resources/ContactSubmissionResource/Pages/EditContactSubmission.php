<?php

namespace App\Filament\Resources\ContactSubmissionResource\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContactSubmission extends EditRecord
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Tandai sebagai dibaca saat admin buka
        if ($data['status'] === 'new') {
            ContactSubmission::find($data['id'])?->update(['status' => 'read']);
            $data['status'] = 'read';
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
