# Security Review — KOS Management

Tanggal: 2026-10-17 · Referensi: OWASP ASVS / Top 10 (diterapkan sesuai stack Laravel + Filament + Sanctum).

## Diperbaiki sesi 100/100 (2026-10-18)

| # | Area | Perbaikan |
|---|---|---|
| 9 | Report date-range | `to` tanggal-polus mengecualikan data hari yang sama → helper `dateRange()` inklusif + konsisten halaman Filament (scope properti di 3 report pages) |
| 10 | Webhook | Verifikasi nominal vs transaksi; guard success (idempotency + out-of-order); unknown order diabaikan |
| 11 | Plugin/theme | `SlugGuard` (regex + containment), validasi manifest + kompatibilitas versi, transaksi DB, `uninstall` jujur, hapus permission mati `system.plugins` |
| 12 | License | Command `license:validate` dibuat (jadwal tidak lagi broken); `dev_bypass` default false + `.env.example` false |
| 13 | Lease workflow | `terminate` tanpa fallback diam-diam + transaksi; `end` transaksi; `giveNotice`/`renew` guard status |
| 14 | Deposit ledger | Refund parsial tidak mengunci; guard settled di markReceived/refund/forfeit |
| 15 | MoveRoom | Kredit prorate bertanda benar; lock + cek operasional; transisi status via service |
| 16 | CheckInOut | Settlement masuk transaksi yang sama (atomik) |
| 17 | EContract | Guard status, occupant-first → `sent`, reset signature saat regenerasi, validasi signature (magic bytes, 2MB), rute portal sign + kirim WA nyata |
| 18 | Utility | Duplikat periode (whereDate + normalisasi), histori invoice imutabel |
| 19 | WA log | Migrasi nullable `notifiable_*` (reversible, MySQL/SQLite) |
| 20 | PSEO | Whitelist kota/kampus/fasilitas/tipe + 404; filter properti per kota; alternatif/bandingkan wajib properti nyata; fix variabel view |
| 21 | Notifikasi | Isolasi tenant + endpoint tenant + hapus + markAll query |
| 22 | Commands | Dedupe notifikasi `lease:check-expiring` + `send-reminders`; status `expiring_soon` + range inklusif |
| 23 | README | Peringatan kredensial demo hanya lokal |

## Diperbaiki sesi sebelumnya (2026-10-17)

| # | Area | Sebelum | Sesudah | Bukti |
|---|---|---|---|---|
| 1 | BOLA/properti | Dashboard admin agregat global tanpa authZ | `dashboard.view` gate + `scopedPropertyIds()` di summary/upcoming/recent | `AdminSecurityHardeningTest` (3 test scope) |
| 2 | Enumerasi user | `GET users` tanpa gate | `ownerOnly()` (`user.manage`) | test 403 vs 200 |
| 3 | BOLA export | Export PDF/Excel lintas properti | `scopedPropertyIds` diteruskan ke Export class + `invoicePdf` | test CSV hanya berisi properti sendiri |
| 4 | Broken access | `User::propertyIds()` tidak ada → 500 | `scopedPropertyIds()` + 403 | test `invoicePdfSingle` lintas properti → 403 |
| 5 | Webhook | Key kosong → signature tertebak; replay memproses ulang | `abort(500)` bila key kosong + cek `success` sebelum proses | 2 test webhook |
| 6 | Akun nonaktif | Token/sesi tetap hidup; `is_active` tidak dicek | `EnsureActiveAccount` middleware (API) + `canAccessPanel` (Filament) + `PortalAuth` cabut sesi | 3 test |
| 7 | Brute force | Login tanpa throttle | `throttle:10,1` di 4 endpoint login | manual (route list) |
| 8 | Logika bisnis | `expiring()`/upcoming kosong untuk lease ≤30 hari | `whereIn(active, expiring_soon)` + range day-safe | test `leases/expiring` |

## Masih baik / tidak ada masalah

- Mass assignment: semua model `$fillable` eksplisit; field sensitif (`role`, `status`, `total`) di-set server-side di controller.
- Signature webhook Midtrans (`sha512`) & Tripay (`hmac-sha256` body) benar; CSRF dikecualikan dengan tepat.
- Tenant isolation: portal/API tenant selalu lewat `occupant_id` sendiri; tidak ada IDOR antar-tenant (tercover test).
- 34 Resource + 10 Pages Filament memakai `AuthorizesAccess`/`AuthorizesPageAccess`; auditor read-only.
- Tidak ada secret di repo; payment memakai sandbox key via settings (DB), bukan env.
- Tidak ada TODO/FIXME, endpoint kosong, atau `eval`/deserialisasi tak aman.

## Sisa risiko / belum selesai (BLOCKED atau butuh aksi manual)

1. **Rate-limit di balik proxy/CDN** — throttle key default pakai IP; pastikan `TrustProxies` dikonfigurasi saat deploy agar tidak semua user berbagi bucket. (P2)
2. **Webhook allowlist IP & timestamp replay window** — signature sudah benar, tapi belum ada allowlist IP Midtrans/Tripay dan batas umur payload. (P2)
3. **`Gate::policy` tidak dipakai** — 9 policy class adalah dead-code; otorisasi lewat `Permissions`+Gate. Jangan dihapus tanpa migrasi referensi (risiko regresi). (DEBT)
4. **License `dev_bypass=true` di `.env`** — pastikan `false` di produksi agar `RequirePair` aktif. (P1 manual — cek runbook)
5. **Backup `backup:run`** — command ada + terjadwal, belum pernah diuji restore sungguhan. (P1 manual)
6. **Upload publik** (`public/images/docs`, foto properti) — pastikan web server menonaktifkan eksekusi PHP di direktori upload. (P2 manual)
