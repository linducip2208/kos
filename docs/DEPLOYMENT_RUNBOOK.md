# Deployment Runbook — KOS Management

## 1. Prasyarat

- PHP 8.3+, Composer, Node 18+, MySQL 8.
- Salin `.env.example` → `.env`, isi `APP_KEY` (`php artisan key:generate`).

## 2. Variabel `.env` penting

| Key | Prod | Catatan |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Jangan `debug=true` di prod (error leakage) |
| `APP_URL` | domain asli (`https://…`) | Dipakai canonical docs, link invoice, webhook |
| `DB_*` | kredensial MySQL prod | — |
| `SESSION_DRIVER` / `CACHE_STORE` / `QUEUE_CONNECTION` | `database` | Butuh tabel `sessions/cache/jobs` (sudah di migrasi) |
| `MAIL_*` | SMTP asli | Default `log` hanya untuk dev |
| `LICENSE_SERVER_URL` | `https://whitelabel.co.id` | Jangan ubah tanpa koordinasi |
| `LICENSE_DEV_BYPASS` | **`false`** | Wajib false agar pairing aktif |
| Trust proxy | atur `TrustProxies` bila di balik Nginx/CDN | Agar throttle + URL benar |

> Semua kredensial gateway (Midtrans/Tripay) & WhatsApp disimpan di tabel `settings` via halaman Pengaturan — BUKAN di `.env`. Isi setelah deploy pertama.

## 3. Urutan deploy aman

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force          # HANYA migrate; jangan fresh/refresh
php artisan license:validate         # lock lisensi valid? gagal = pairing ulang via /__pair
php artisan db:seed --class=UserSeeder --force   # bila butuh akun awal (idempoten via updateOrCreate)
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
```

Rollback: `php artisan migrate:rollback --step=1` (semua migrasi baru sesi ini reversible; tidak ada alter tabel lama).

## 4. Scheduler & queue (wajib)

```cron
* * * * * php /path/artisan schedule:run >> /dev/null 2>&1
```

Jadwal aktif: `license:validate` 05:00, `invoices:generate` 06:00, `invoices:mark-overdue` 07:00,
`invoices:send-reminders` 08:00, `lease:check-expiring` Senin 09:00, `backup:run` 02:00, `seo:indexnow` 02:45.
Queue: jalankan `php artisan queue:work --tries=3` di bawah supervisor/systemd (driver `database`).

## 5. Health checks pasca-deploy

```bash
php artisan route:list --except-vendor | head
php artisan migrate --pretend
php artisan test            # atau subset: --filter=AdminSecurityHardeningTest
curl -f https://DOMAIN/up
curl -f https://DOMAIN/docs
```

Login: `/admin` (owner), `/portal/login` (penyewa), test 1 transaksi manual + 1 webhook sandbox.

## 6. Backup & restore

- Otomatis: `backup:run --only-db --keep=7` tiap 02:00. Simpan salinan off-server.
- Restore: buat DB kosong → `mysql < backup.sql` → `php artisan migrate --force` (pastikan no pending) → verifikasi login.
- **Restore belum pernah diuji end-to-end — lakukan drill sebelum go-live.**

## 7. Troubleshooting

| Gejala | Cek |
|---|---|
| 404 `/docs` di `php artisan serve` | Jangan ada folder `public/docs` (pernah menabrak route; sudah dipindah ke `public/images/docs`) |
| 403 massal Filament | `users.is_active` + gate `Permissions::MATRIX` |
| Webhook 500 | Isi server/private key gateway di Pengaturan dulu |
| Invoice tidak generate | Scheduler + queue jalan? `jobs`/`failed_jobs` |
| License terkunci | `php artisan license:validate`, cek `storage/app/.license.lock` |
