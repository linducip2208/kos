<?php

namespace App\Services;

use App\Models\WhatsappLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private ?string $apiKey;
    private ?string $sender;
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey  = setting('whatsapp_api_key', null, 'notif');
        $this->sender  = setting('whatsapp_sender', null, 'notif');
        $this->enabled = (bool) setting('whatsapp_enabled', false, 'notif');
    }

    public function send(string $number, string $message, string $type = 'custom', ?object $notifiable = null): bool
    {
        $number = $this->normalizeNumber($number);

        $log = WhatsappLog::create([
            'to_number'       => $number,
            'to_name'         => $notifiable?->name ?? null,
            'message'         => $message,
            'type'            => $type,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id'   => $notifiable?->id ?? null,
            'status'          => 'queued',
        ]);

        if (!$this->enabled || !$this->apiKey) {
            Log::info("WA [DISABLED] → {$number}: " . substr($message, 0, 50));
            return false;
        }

        try {
            $response = Http::timeout(15)->post('https://api.fonnte.com/send', [
                'target'  => $number,
                'message' => $message,
                'token'   => $this->apiKey,
            ]);

            $success = $response->successful() && ($response->json('status') ?? false);

            $log->update([
                'status'           => $success ? 'sent' : 'failed',
                'gateway_response' => $response->json(),
                'sent_at'          => $success ? now() : null,
                'error_message'    => $success ? null : ($response->json('reason') ?? 'Unknown error'),
            ]);

            return $success;
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error("WA send failed to {$number}: " . $e->getMessage());
            return false;
        }
    }

    public function sendInvoice(\App\Models\Invoice $invoice): bool
    {
        $occupant = $invoice->lease->occupant;
        $room     = $invoice->lease->room;
        $appName  = setting('app_name', 'Kos Manager');

        $message = "📋 *TAGIHAN SEWA - {$appName}*\n\n"
            . "Halo *{$occupant->name}*,\n\n"
            . "Berikut tagihan sewa Anda:\n"
            . "🏠 Kamar: *{$room->room_number}*\n"
            . "📅 Periode: " . $invoice->period_start->format('d M Y') . " – " . $invoice->period_end->format('d M Y') . "\n"
            . "⏰ Jatuh Tempo: *" . $invoice->due_date->format('d M Y') . "*\n"
            . "💰 Total: *Rp " . number_format($invoice->total, 0, ',', '.') . "*\n\n"
            . "Harap melakukan pembayaran sebelum jatuh tempo.\n"
            . "Terima kasih 🙏";

        return $this->send($occupant->whatsapp_number, $message, 'invoice', $invoice);
    }

    public function sendReminder(\App\Models\Invoice $invoice, int $daysBefore): bool
    {
        $occupant = $invoice->lease->occupant;
        $appName  = setting('app_name', 'Kos Manager');

        $message = "⏰ *REMINDER TAGIHAN - {$appName}*\n\n"
            . "Halo *{$occupant->name}*,\n\n"
            . "Tagihan sewa Anda akan jatuh tempo *{$daysBefore} hari lagi* "
            . "(" . $invoice->due_date->format('d M Y') . ").\n\n"
            . "💰 Total: *Rp " . number_format($invoice->total, 0, ',', '.') . "*\n\n"
            . "Segera lakukan pembayaran untuk menghindari denda keterlambatan.\n"
            . "Terima kasih 🙏";

        return $this->send($occupant->whatsapp_number, $message, 'reminder', $invoice);
    }

    public function sendOverdueNotice(\App\Models\Invoice $invoice): bool
    {
        $occupant = $invoice->lease->occupant;
        $appName  = setting('app_name', 'Kos Manager');
        $penalty  = $invoice->penalty > 0
            ? "\n⚠️ Denda keterlambatan: *Rp " . number_format($invoice->penalty, 0, ',', '.') . "*"
            : '';

        $message = "🔴 *TAGIHAN JATUH TEMPO - {$appName}*\n\n"
            . "Halo *{$occupant->name}*,\n\n"
            . "Tagihan sewa Anda telah *melewati jatuh tempo*.\n\n"
            . "💰 Pokok: Rp " . number_format($invoice->total - $invoice->penalty, 0, ',', '.')
            . $penalty . "\n"
            . "📊 Total Tagihan: *Rp " . number_format($invoice->total + $invoice->penalty, 0, ',', '.') . "*\n\n"
            . "Harap segera melakukan pembayaran.\n"
            . "Info lebih lanjut hubungi kami langsung.";

        return $this->send($occupant->whatsapp_number, $message, 'overdue', $invoice);
    }

    public function sendWelcome(\App\Models\Occupant $occupant, string $portalUrl, string $tempPassword): bool
    {
        $appName = setting('app_name', 'Kos Manager');
        $message = "🏠 *SELAMAT DATANG - {$appName}*\n\n"
            . "Halo *{$occupant->name}*!\n\n"
            . "Akun portal penyewa Anda telah dibuat.\n\n"
            . "🔗 Portal: {$portalUrl}\n"
            . "📧 Email: {$occupant->email}\n"
            . "🔑 Password Sementara: *{$tempPassword}*\n\n"
            . "Segera ganti password setelah login pertama.\n"
            . "Terima kasih telah memilih kos kami 🙏";

        return $this->send($occupant->whatsapp_number, $message, 'welcome', $occupant);
    }

    public function blast(array $numbers, string $message): int
    {
        $sent = 0;
        foreach ($numbers as $number) {
            if ($this->send($number, $message, 'blast')) {
                $sent++;
            }
        }
        return $sent;
    }

    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }
        if (!str_starts_with($number, '62')) {
            $number = '62' . $number;
        }
        return $number;
    }
}
