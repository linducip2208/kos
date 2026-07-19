<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Notifications\Notification;

class InvoiceDueNotification extends Notification
{
    public function __construct(public Invoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'invoice_due',
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'due_date'       => $this->invoice->due_date,
            'total'          => $this->invoice->total,
            'message'        => "Tagihan {$this->invoice->invoice_number} jatuh tempo pada {$this->invoice->due_date}.",
        ];
    }
}
