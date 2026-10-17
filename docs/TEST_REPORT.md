# Test Report — KOS Management

Tanggal: 2026-10-17 · Runner: `php artisan test` (PHPUnit, SQLite in-memory, sesuai `phpunit.xml`).

## Hasil akhir (full suite)

- **295 passed, 0 failed — 628 assertions — ±45 detik.**
- Baseline sebelum sesi: 279 passed / 591 assertions. Sesi ini: **+16 test, +37 assertions.**

## Test baru sesi ini

| File | Jumlah | Cakupan |
|---|---|---|
| `tests/Feature/Api/AdminSecurityHardeningTest.php` | 11 | Scope dashboard (summary/upcoming/recent), `users` 403 vs 200, token nonaktif admin & tenant, scope export CSV, PDF lintas properti → 403, webhook 403/500/idempotency, `leases/expiring` |
| `tests/Feature/WhatsAppBlastPageTest.php` | 2 | Akses halaman blast (owner 200 / maintenance 403), user nonaktif 403 di panel |
| `tests/Unit/Services/UtilityRateServiceTest.php` | 3 | Tiered rate, integrasi `UtilityService`, kalkulasi minimum + charges |

## Perintah yang dijalankan (semua PASS)

```bash
php artisan test --filter=AdminSecurityHardeningTest   # 11 passed (iterasi, sempat 2 gagal → diperbaiki)
php artisan test --filter="UtilityRateServiceTest|UtilityServiceTest"  # 9 passed
php artisan test --filter="WhatsAppBlastPageTest"      # 2 passed
php artisan test --filter="LandingTest|DemoAccountsTest"  # 19 passed
php artisan test                                        # 295 passed (final)
php vendor/bin/pint --test <18 file>                    # NOT CLEAN (pre-existing, lihat bawah)
php artisan route:list --except-vendor                   # 248 baris, tidak ada route hilang
```

## Failure selama sesi (diperbaiki, bukan diabaikan)

1. `upcoming` kosong — akar: lease ≤30 hari berstatus `expiring_soon` + `whereBetween(now(),…)` ber-waktu. Fix di controller.
2. Webhook test 500 — perilaku fail-closed yang benar; test disesuaikan (skenario key kosong vs signature salah dipisah).
3. `/admin/whatsapp-blast` 404 — slug Filament `whats-app-blast`; fix via `$slug` eksplisit.

## Tidak dijalankan (BLOCKED / manual)

- `npm run build` — tidak ada perubahan frontend/Vite sesi ini; tidak diperlukan.
- Restore backup drill, webhook gateway asli, WA blast sungguhan — butuh kredensial/layanan eksternal (dilarang misi).
- `pint --test` repo-wide NOT CLEAN termasuk file yang tidak saya sentuh (`Lease.php` dsb) → style drift pre-existing; `pint --fix` sengaja TIDAK dijalankan agar diff minimal.
- Test PSEO (15+ route), install plugin/theme, license server — GAP tercatat di FEATURE_MATRIX.
