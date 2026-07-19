<?php

namespace App\Notifications;

use App\Models\Lease;
use Illuminate\Notifications\Notification;

class LeaseExpiringNotification extends Notification
{
    public function __construct(public Lease $lease, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'lease_expiring',
            'lease_id'     => $this->lease->id,
            'lease_number' => $this->lease->lease_number,
            'end_date'     => $this->lease->end_date,
            'days_left'    => $this->daysLeft,
            'message'      => "Kontrak {$this->lease->lease_number} akan berakhir dalam {$this->daysLeft} hari ({$this->lease->end_date}).",
        ];
    }
}
