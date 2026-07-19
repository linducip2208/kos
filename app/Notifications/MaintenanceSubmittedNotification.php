<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Notifications\Notification;

class MaintenanceSubmittedNotification extends Notification
{
    public function __construct(public MaintenanceRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'               => 'maintenance_submitted',
            'maintenance_id'     => $this->request->id,
            'title'              => $this->request->title ?? 'Permintaan perawatan',
            'room'               => $this->request->room?->room_number,
            'message'            => "Permintaan perawatan baru dari kamar {$this->request->room?->room_number}.",
        ];
    }
}
