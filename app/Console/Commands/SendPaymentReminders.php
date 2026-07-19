<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceDueNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPaymentReminders extends Command
{
    protected $signature   = 'invoices:send-reminders';
    protected $description = 'Kirim reminder WhatsApp untuk tagihan yang akan jatuh tempo';

    public function handle(): int
    {
        if (!setting('whatsapp_enabled', false, 'notif')) {
            $this->warn('WhatsApp notif tidak aktif. Aktifkan di Pengaturan Umum.');
            return self::SUCCESS;
        }

        $reminderDays = (int) setting('reminder_days', 3);
        $targetDate   = Carbon::today()->addDays($reminderDays)->toDateString();

        $invoices = Invoice::with(['lease.occupant', 'lease.room'])
            ->whereIn('status', ['sent'])
            ->whereDate('due_date', $targetDate)
            ->whereNull('reminder_sent_at')
            ->get();

        // Kirim in-app notification ke semua admin/owner
        $admins = User::whereIn('role', ['owner', 'staff'])->where('is_active', true)->get();

        $sent = 0;
        foreach ($invoices as $invoice) {
            // In-app notification
            foreach ($admins as $admin) {
                $admin->notify(new InvoiceDueNotification($invoice));
            }

            $occupant = $invoice->lease->occupant;
            $phone    = $occupant->whatsapp_number ?? $occupant->whatsapp ?? $occupant->phone;
            $message  = $this->buildMessage($invoice);

            if ($this->sendWhatsApp($phone, $message)) {
                $invoice->update(['reminder_sent_at' => now()]);
                $sent++;
                $this->line("  Terkirim → {$occupant->name} ({$phone})");
            }
        }

        $this->info("Reminder terkirim: {$sent}");
        return self::SUCCESS;
    }

    private function buildMessage(Invoice $invoice): string
    {
        $occupant  = $invoice->lease->occupant;
        $room      = $invoice->lease->room;
        $dueDate   = $invoice->due_date->format('d/m/Y');
        $total     = 'Rp ' . number_format($invoice->total, 0, ',', '.');
        $appName   = setting('app_name', 'Kos Manager');

        return "Halo *{$occupant->name}*,\n\n"
            . "Ini adalah pengingat bahwa tagihan sewa kamar *{$room->room_number}* akan jatuh tempo pada *{$dueDate}*.\n\n"
            . "Total Tagihan: *{$total}*\n"
            . "No. Invoice: {$invoice->invoice_number}\n\n"
            . "Mohon segera lakukan pembayaran. Terima kasih.\n\n"
            . "— {$appName}";
    }

    private function sendWhatsApp(string $phone, string $message): bool
    {
        $apiKey  = setting('whatsapp_api_key', '', 'notif');
        $sender  = setting('whatsapp_sender', '', 'notif');

        if (empty($apiKey) || empty($sender)) return false;

        try {
            $response = Http::withHeaders(['Authorization' => $apiKey])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target'  => $phone,
                    'message' => $message,
                    'sender'  => $sender,
                ]);
            return $response->successful() && ($response->json('status') === true);
        } catch (\Exception $e) {
            $this->error("Gagal kirim ke {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
