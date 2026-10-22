<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\RateLimiter;

class SendWhatsAppBlast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    /** @param string[] $numbers */
    public function __construct(
        public array $numbers,
        public string $message,
    ) {}

    public function handle(): void
    {
        foreach ($this->numbers as $number) {
            // Rate-limit gateway: maks 1 pesan/detik agar tidak di-throttle.
            RateLimiter::attempt('wa-blast', 1, fn () => true, 1);
            SendWhatsAppMessage::dispatch($number, $this->message, 'blast');
        }
    }
}
