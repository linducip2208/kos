<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Validasi lisensi ke whitelabel.co.id, perbarui checksum
Schedule::command('license:validate')->dailyAt('05:00');

// Generate tagihan otomatis
Schedule::command('invoices:generate')->dailyAt('06:00');

// Tandai overdue & hitung denda
Schedule::command('invoices:mark-overdue')->dailyAt('07:00');

// Kirim reminder WhatsApp/notifikasi tagihan jatuh tempo
Schedule::command('invoices:send-reminders')->dailyAt('08:00');

// Cek kontrak yang akan berakhir dalam 30 hari, setiap Senin pagi
Schedule::command('lease:check-expiring --days=30')->weeklyOn(1, '09:00');

// Backup database setiap hari jam 02:00, simpan 7 file terakhir
Schedule::command('backup:run --only-db --keep=7')->dailyAt('02:00');

// IndexNow — submit URL baru ke search engine (Bing, Yandex, Seznam, Naver)
Schedule::command('seo:indexnow --new')->dailyAt('02:45');
