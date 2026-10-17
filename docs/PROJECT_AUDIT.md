# Project Audit — KOS / Kost Management

Tanggal: 2026-10-17 · Branch: `main` · HEAD: `047233a`
Auditor: OpenCode (Principal Engineer mode) · Scope: full repository, read + implement + test.

## 1. Stack aktual (dari file, bukan asumsi)

| Layer | Temuan |
|---|---|
| Runtime | PHP 8.3, Laravel 13, Composer |
| Admin panel | Filament v5 (`app/Filament`: 34 Resource, 10 Pages, Widgets) |
| Auth API | Laravel Sanctum; guard `web` (User) + guard `portal` (Occupant) |
| Database | MySQL 8 (prod/dev), SQLite in-memory (test) |
| Queue/Session/Cache | database driver (tabel jobs/sessions/cache) |
| Payment | Midtrans Snap + Tripay + manual (`PaymentGatewayService`) |
| Notifikasi | WhatsApp via `WhatsAppService` (log `whatsapp_logs`), email via `log` mailer (dev) |
| Frontend publik | Blade + Tailwind CDN (landing, blog, docs, portal) |
| Build | Vite (`vite.config.js`), npm |
| Lisensi | Pairing v3 (`LicenseClient`, `RequirePair` di grup `web`) |
| Schedule | 7 jadwal di `routes/console.php`, 18 console commands |

## 2. Inventaris kode

- `app/Models`: 38 model, semua `$fillable` eksplisit (tidak ada `$guarded = []`).
- `app/Services`: 14 service (2 sempat dead: `WhatsAppService`, `UtilityRateService` — kini keduanya live).
- `app/Policies`: 9 policy class, tapi otorisasi runtime lewat `Permissions` + Gate + trait `AuthorizesAccess`/`AuthorizesPageAccess` (policy class tidak direferensikan Gate::$policies — dead-code arsitektural, tidak dihapus agar tidak merusak apa pun).
- `database/migrations`: 47 file (36 create, sisanya alter + 1 normalisasi role).
- `routes`: `web.php` (landing, portal, booking, print, blog, docs, pseo ±15 route, webhook, pair), `api.php` (v1 public/admin/tenant + legacy), `console.php`, `pair-routes.php`.
- `tests`: 30+ file (unit + feature + API).

## 3. Baseline test (sebelum perubahan)

`php artisan test` → **279 passed (591 assertions)**. Git bersih kecuali perubahan docs sesi sebelumnya (`DocsController`, `docs-index.blade`, `public/images/`), yang dipertahankan.

## 4. Temuan audit (ringkas; detail di SECURITY_REVIEW.md)

| ID | Severity | Temuan |
|---|---|---|
| P0-1 | Kritis | 3 endpoint `Admin\DashboardController` tanpa Gate + tanpa scoping properti |
| P0-2 | Kritis | `GET /v1/admin/users` tanpa `ownerOnly` → enumerasi user + PII |
| P1-1 | Tinggi | Export Excel/PDF tanpa scoping properti |
| P1-2 | Tinggi | `ExportController::canAccessProperty` memanggil `User::propertyIds()` yang tidak ada → 500 untuk user scoped |
| P1-3 | Tinggi | Webhook tanpa fail-closed saat key kosong + tanpa idempotency |
| P1-4 | Tinggi | User `is_active=false` tetap bisa login panel + pakai token API; sesi portal tidak dicabut saat `portal_active=false` |
| P1-5 | Tinggi | `LeaseController::expiring` + dashboard `upcoming` filter `status=active` padahal lease ≤30 hari otomatis jadi `expiring_soon` → endpoint selalu kosong |
| P2-1 | Sedang | `WhatsAppBlast` halaman mati; `UtilityRateService` tidak dipakai |
| P2-2 | Sedang | Login tanpa rate-limit |
| P2-3 | Sedang | `whereBetween end_date` pakai `now()` ber-waktu (off-by-timezone) |

Semua temuan di atas **sudah diperbaiki** pada sesi ini (lihat FEATURE_MATRIX kolom tindakan + git diff).
Tidak ditemukan: TODO/FIXME, endpoint kosong, mass-assignment liar, CSRF/XSS/SQLi aktif.
