# Test Report — KOS Management

Tanggal: 2026-10-17 · Runner: `php artisan test` (PHPUnit, SQLite in-memory, sesuai `phpunit.xml`).

## Hasil akhir (full suite) — sesi 100/100 (2026-10-18)

- **354 passed, 0 failed — 815 assertions — ±110 detik.**
- Baseline sesi lalu: 295 passed / 628 assertions. Total sesi ini: **+59 test, +187 assertions.**

## Test baru sesi ini (57)

| File | Jumlah | Cakupan |
|---|---|---|
| `tests/Unit/Services/DepositLedgerTest.php` | 10 | Refund parsial/ganda, forfeit ganda, markReceived settled, terminate/giveNotice/renew guard |
| `tests/Unit/Services/MoveRoomServiceTest.php` | 4 | Kredit vs tagihan prorate, lease ended, tujuan sama/occupied |
| `tests/Unit/Services/CheckInOutServiceTest.php` | 5 | Check-in/out, duplikat, tanpa check-in, settlement deposit atomik, nominal negatif |
| `tests/Unit/Services/UtilityBillingTest.php` | 5 | Duplikat periode, nol, reset meter, imutabilitas histori, double-add invoice |
| `tests/Unit/Services/WhatsAppServiceTest.php` | 5 | Disabled, sukses, gagal provider, exception, blast count |
| `tests/Feature/EContractTest.php` | 5 | Urutan tanda tangan, final guard, signature invalid, regenerasi, portal token flow |
| `tests/Feature/PseoRoutesTest.php` | 4 | 404 kota/kampus/fasilitas fiktif, filter kota, alternatif/bandingkan |
| `tests/Feature/Api/ReportEndpointsTest.php` | 5 | Revenue/overdue/turnover/occupancy, scope, empty, 403 |
| `tests/Feature/Api/NotificationLifecycleTest.php` | 2 | Admin lifecycle + hapus, isolasi tenant |
| `tests/Feature/Commands/ReminderCommandsTest.php` | 2 | Dedupe lease-expiring + send-reminders |
| `tests/Feature/Commands/LicenseValidateCommandTest.php` | 3 | Fail-closed tanpa lock, sukses terverifikasi, schedule |
| `tests/Feature/Api/PluginThemeLifecycleTest.php` | 4 | Lifecycle install→uninstall, traversal/manifest/versi, theme, API 422 |
| `tests/Feature/PrintRenderTest.php` | 2 | PDF invoice/kwitansi/laporan ter-render (owner + tenant) |
| Perluasan `AdminSecurityHardeningTest.php` | 3 | Webhook nominal salah, out-of-order, order asing |

## Perintah yang dijalankan (semua PASS)

```bash
composer validate --no-check-publish          # valid
php artisan optimize:clear                   # DONE
php artisan route:list --except-vendor        # 257 baris, tidak ada route hilang
php artisan migrate --pretend                # OK termasuk migrasi baru
php artisan test                              # 354 passed, 815 assertions (final)
php vendor/bin/pint --test <file baru>        # passed (4 file test di-fix otomatis)
php vendor/bin/pint --test <file lama diubah> # NOT CLEAN pre-existing (tidak di-fix massal)
```

## Failure yang ditemukan & diperbaiki (tidak diabaikan)

Sesi lalu: `upcoming` kosong (status `expiring_soon` + range ber-waktu); webhook 500 fail-closed; slug `whats-app-blast`.

Sesi ini:
1. `ReportController` filter `to` tanggal-polus mengecualikan data hari yang sama → helper `dateRange()` inklusif.
2. Webhook nominal salah → invoice tidak lunas; out-of-order deny → tidak downgrade success.
3. `recordReading` duplikat periode → UNIQUE violation (cast date vs lookup) → `whereDate` + normalisasi awal bulan.
4. `WhatsAppService::send` tanpa notifiable → crash NOT NULL → migrasi nullable baru (reversible, MySQL/SQLite).
5. View `pseo/kos-fasilitas` variabel `$fasilitasName` hilang → 500 → controller kirim alias.
6. Rute combo kampus memakai dash (`/kos-dekat-itb-di-bandung`).
7. Checkout settlement di luar transaksi → dipindah ke dalam transaksi yang sama.

## Tidak dijalankan (BLOCKED / manual)

- `npm run build` — tidak ada perubahan frontend/Vite sesi ini; tidak diperlukan.
- Restore backup drill, webhook gateway asli, WA blast sungguhan — butuh kredensial/layanan eksternal (dilarang misi).
- `pint --test` repo-wide NOT CLEAN termasuk file yang tidak saya sentuh (`Lease.php` dsb) → style drift pre-existing; `pint --fix` massal sengaja TIDAK dijalankan agar diff minimal.
- Browser/E2E manual (responsivitas mobile, visual) — tooling browser tidak tersedia → BLOCKED, tandai verifikasi manual.
