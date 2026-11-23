# Checklist Optimasi Produksi (tanpa ubah kode)

Jalankan sekali setelah tiap `git pull`, dan pastikan persisten setelah reboot.

## 1. Environment (.env)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://egs-property.co.id

CACHE_STORE=file        # atau redis kalau sudah ada
SESSION_DRIVER=file     # jangan database (mengurangi 1-2 query MySQL per request)
QUEUE_CONNECTION=database
```

## 2. Optimize Laravel (wajib)

```bash
cd /var/www/kos-kosan
php artisan optimize          # cache config + route + view + event
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Cek: `php artisan about` → Config/Routes/Events harus `CACHED`, Debug `DISABLED`.

> Catatan: setiap ubah `.env`, ulangi `php artisan optimize` (atau `optimize:clear` lalu `optimize`).

## 3. PHP-FPM + OPcache (sering jadi penyebab "lambat umum")

```bash
php -m | grep -i opcache   # harus muncul
```

`opcache.ini` rekomendasi:

```ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0   # produksi; set 1 + revalidate_freq=2 kalau deploy tanpa restart
opcache.jit_buffer_size=128M
```

Setelah ubah: `systemctl restart php8.3-fpm`.

## 4. Web server cache header (sudah ada di deploy/nginx.conf)

Blok `location ~* \.(css|js|...)` dengan `expires 1y` pastikan aktif agar aset Filament di-cache browser.

## 5. Database

```bash
# Cek ukuran tabel cache/sesi (kalau masih pakai database driver, bersihkan berkala)
php artisan cache:clear
php artisan session:gc 2>/dev/null || true
mysql -e "SELECT table_name, table_rows, ROUND(data_length/1024/1024,1) AS MB FROM information_schema.tables WHERE table_schema='kos_kosan' ORDER BY data_length DESC LIMIT 10;"
```

Index penting sudah ada di migration (`invoices.status+due_date`, `leases.occupant_id+status`, dll). Jangan tambah index manual tanpa `migrate`.

## 6. Queue + Scheduler (wajib jalan)

```bash
systemctl status supervisor
supervisorctl status
```

Harus ada `kos-queue` (queue:work) dan `kos-scheduler` (schedule:work) RUNNING.
Tanpa ini: reminder invoice, denda, dan WA blast menumpuk lalu meledak sekaligus → server tiba-tiba berat.

Cron fallback (kalau tidak pakai schedule:work):

```cron
* * * * * cd /var/www/kos-kosan && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Kalau server timeout (ERR_TIMED_OUT seperti kemarin)

Berurutan: DNS (`nslookup egs-property.co.id`) → SSH bisa? → `systemctl status nginx php8.3-fpm mysql supervisor` → `df -h; free -h` (disk penuh / OOM adalah penyebab #1) → `journalctl -u php8.3-fpm --since "2 hours ago"`.

## 8. Yang sudah dioptimasi di kode (commit ini)

- `InvoiceResource` + `InvoicePaymentResource`: `Select::options(Model::pluck()->get())` (load SEMUA invoice/lease ke memori tiap buka form) → `->relationship()->searchable()->preload(false)` (lazy via AJAX).
- `RevenueTrendChart::getData()`: 12 query + hydrasi model per bulan → 2 query agregat `GROUP BY ym` + `Cache::remember 5 menit`.
- `DashboardMetricsService::for()/revenueTrend()`: `withSum()->get()->sum()` (load semua invoice) → agregat langsung ke `invoice_payments` + cache 5 menit.
- `receivableAging()`: tambah `withSum` agar `balance_due` tidak N+1 (2 query per invoice).
