# Security Review — KOS Management

Tanggal: 2026-10-17 · Referensi: OWASP ASVS / Top 10 (diterapkan sesuai stack Laravel + Filament + Sanctum).

## Diperbaiki sesi ini

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
