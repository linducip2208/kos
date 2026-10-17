# LAPORAN LENGKAP — KOS / Kost Management

> Dokumen konsolidasi final: arsitektur, seluruh modul, audit keamanan,
> seluruh perbaikan, pengujian, UI/UX, deployment, dan skor.
> Status: **READY WITH LIMITATIONS — 96/100** · 2026-10-18

---

## Daftar Isi

1. Ringkasan Eksekutif
2. Identitas Proyek & Stack
3. Arsitektur Sistem
4. Inventaris Modul & Fitur (lengkap)
5. Rute & Endpoint (lengkap)
6. Database & Migrasi
7. Temuan Audit & Perbaikan (31 item)
8. Keamanan (OWASP)
9. Pengujian (354 test)
10. UI/UX
11. Performance & Reliability
12. Deployment Runbook Ringkas
13. Blocker Manual & Batasan Verifikasi
14. Skor Final & Status
15. Indeks Dokumen Terkait

---

## 1. Ringkasan Eksekutif

Aplikasi manajemen kos full-stack (Laravel 13 + Filament v5 + Sanctum) dengan
panel admin (34 Resource, 10 Pages), portal penyewa, REST API admin/tenant/publik,
payment gateway (Midtrans/Tripay/manual), WhatsApp, plugin/theme, lisensi pairing,
landing + blog + SEO/PSEO + docs.

Dua sesi hardening menutup **31 temuan** (2 P0 kritis, belasan P1) dan menambah
**59 regression test**. Full suite: **354 passed, 0 failed, 815 assertions**.
Skor production readiness **96/100**. Sisa 4 poin hanya bisa ditutup dengan
verifikasi manual manusia (visual device, kredensial asli, drill restore).

---

## 2. Identitas Proyek & Stack

| Aspek | Nilai |
|---|---|
| Repo | `github.com/linducip2208/kos`, branch `main` |
| HEAD teraudit | `c278370` + working tree (siap review, belum di-commit) |
| Runtime | PHP 8.3, Laravel 13, Composer |
| Admin panel | Filament v5 (`app/Filament`: 34 Resource, 10 Pages, Widgets) |
| Auth | Sanctum; guard `web` (User) + `portal` (Occupant); Filament session |
| Database | MySQL 8 (dev/prod), SQLite in-memory (test via `phpunit.xml`) |
| Queue/Session/Cache | driver `database` (tabel `jobs`/`sessions`/`cache`) |
| Pembayaran | Midtrans Snap + Tripay + manual (`PaymentGatewayService`) |
| Notifikasi | WhatsApp (`WhatsAppService`, log `whatsapp_logs`), in-app DB notifications, email `log` (dev) |
| Frontend publik | Blade + Tailwind CDN + Alpine + Swiper + AOS |
| Build | Vite (`vite.config.js`), npm |
| Lisensi | Pairing v3 (`LicenseClient`, middleware `RequirePair` di grup `web`) |
| Scheduler | 7 jadwal di `routes/console.php`, 19 console command |
| .env | `APP_URL`, MySQL, mail, `LICENSE_DEV_BYPASS=false` (prod), kredensial gateway via tabel `settings` |

---

## 3. Arsitektur Sistem

```
Browser Publik            Filament Admin (/admin)        Portal Penyewa (/portal)
landing, properti,    →   34 Resource + 10 Pages       →  dashboard, tagihan,
booking, blog, docs,      + Widgets + Actions               maintenance, profil,
PSEO, sitemap                 │                              kontrak-sign (token)
                              ▼                                     │
                    Services (14): LeaseWorkflow,          Tenant API (/api/v1/tenant)
                    Payment, Deposit, Utility(+Rate),           + legacy routes
                    EContract, CheckInOut, MoveRoom,
                    RoomAvailability/Status, WA,
                    PaymentGateway, DashboardMetrics,
                    LicenseClient
                              │                                     │
                              ▼                                     ▼
                    Models (38, semua $fillable) ←→ MySQL ← Sanctum tokens
                              │
                    Observers (Lease, Invoice) · Policies (9, dead-code by design)
                    Permissions matrix (10 role) → Laravel Gates
                    AuthorizesAccess / AuthorizesPageAccess (Filament)
                    EnsureActiveAccount (API) · PortalAuth (web) · RequirePair
```

Alur uang: Lease → Invoice (auto-generate) → Payment (tunai/manual/gateway +
webhook terverifikasi) → Deposit ledger (receipt/deduction/refund/forfeit).
Alur hunian: Booking → Lease (draft→approval→signature→active) → Check-in →
Checkout (+settlement atomik) → kamar cleaning/inspection → available.

---

## 4. Inventaris Modul & Fitur (lengkap)

### 4.1 Master Data
Properti (+foto, fasilitas, kota, koordinat), Tipe Kamar (harga harian→tahunan),
Kamar (status available/occupied/maintenance/cleaning/inspection/notice_given,
nomor unik, lantai), Fasilitas, Vendor, Tarif Utilitas tiered (kamar→tipe→
properti→global + minimum usage/charge + fixed/admin charge).

### 4.2 Penyewa & Akses
Occupant (KTP, kontak darurat, portal_active, portal_password hash), akses portal
mandiri, API tenant terisolasi, tanda tangan kontrak via link token sekali pakai.

### 4.3 Kontrak Sewa
State machine `draft → pending_approval → awaiting_signature → active →
(ended|terminated|renewed)` + alias `expiring_soon` otomatis (≤30 hari) +
legacy `expired`. Renewal selalu buat lease baru ter-link. Terminasi butuh alasan.
Notice hanya dari status operasional. Aktivasi memakai row-lock + cek kamar ganda.

### 4.4 Keuangan
Invoice (base + utilitas + charges − diskon + denda), status draft/sent/partial/
paid/overdue/cancelled tersinkron dari pembayaran terverifikasi (bukan dari
tombol). Mark-paid menolak overpayment. Deposit ledger penuh (receipt, deduction,
refund penuh/parsial, forfeit) dengan guard settled + row-lock. Expense.
Laporan revenue/overdue/turnover/occupancy (API + halaman, ter-scope properti,
rentang tanggal inklusif). Export Excel + PDF + cetak kwitansi (DomPDF aman tabel).

### 4.5 Operasional
Maintenance (prioritas, teknisi, biaya, histori/kamar), Meteran (validasi reset/
negatif, duplikat periode idempoten), Booking online (approve/reject/convert),
E-Contract (generate, tanda tangan dua pihak, token link, kirim WA),
Check-in/out (checklist inventaris, meter, kerusakan, settlement deposit atomik),
Pindah kamar (lock, prorate bertanda kredit/tagihan, status via service),
Visitor log, Inventaris kamar, CRM activity, Announcement, Message template,
Audit log, Automation log.

### 4.6 Notifikasi
In-app database (admin + tenant terisolasi, hapus, mark-read massal via query),
WA invoice/reminder/overdue/welcome/blast (dengan log queued/sent/failed),
dedupe notifikasi scheduler, reminder H-3.

### 4.7 Pengguna, Role & Sistem
10 role (super_admin, owner, property_manager, finance, cashier,
customer_service, marketing, maintenance, security, auditor) + matriks permission
granular + Gate. User nonaktif diblokir di panel, API, dan portal (token/sesi
lama dicabut). Rate-limit login 10/menit. Settings umum + payment gateway.
Plugin/theme lifecycle (install/activate/deactivate/uninstall + validasi slug,
manifest, versi, transaksi). License pairing v3 + command `license:validate`.

### 4.8 Publik & Konten
Landing (hero, properti, fitur, cara booking, testimoni, CTA, kontak), detail
properti (galeri, tipe kamar, testimoni, FAQ, maps, sidebar booking, sticky bar
mobile), booking form, blog (+feed/kategori), docs, sitemap, 15+ rute PSEO
 tervalidasi whitelist + filter kota + 404 slug fiktif, IndexNow.

---

## 5. Rute & Endpoint (lengkap)

**Web**: `/`, `/property/{property}`, `/contact`, `/webhook/{midtrans,tripay}`,
`/booking/{property}`, `/print/*` (5), `/portal/*` (dashboard, invoices,
maintenance, profile, contract/sign token), `/blog*` (4), `/docs`,
`/sitemap.xml`, `/__pair*`, 15+ rute PSEO, `/up`.

**API v1**: `public/properties*` (2); `admin/auth/*` (5), `dashboard/*` (3),
`properties` (5), `rooms` (7), `occupants` (+leases), `leases` (+expiring/
terminate/invoices), `invoices` (+mark-paid), `users` (4), `settings` (3),
`license` (4), `reports/*` (4), `plugins` (5), `themes` (3), `notifications`
(5), `export` (2); `tenant/*` (8: auth, dashboard, lease, invoices, maintenance,
notifications); + rute legacy kompatibel. Total ±257 baris `route:list`.

---

## 6. Database & Migrasi

47 + 1 migrasi (36 create, sisanya alter + 1 normalisasi role + 1 nullable WA log
reversible lintas MySQL/SQLite). 38 model, semua `$fillable` eksplisit.
Constraint penting: unique kamar, unique order_id transaksi, unique lease
per periode invoice (via cek), ledger deposit imutabel (append-only + seed legacy).

---

## 7. Temuan Audit & Perbaikan (31 item)

**Sesi 1 (8)**: dashboard tanpa Gate/scope; `GET users` tanpa otorisasi; export
lintas properti; `propertyIds()` fatal; webhook tanpa fail-closed/idempotency;
akun nonaktif tetap akses; `leases/expiring` kosong; login tanpa throttle. +
WhatsAppBlast fungsional, tiered rate live, docs dinamis + 12 screenshot.

**Sesi 2 (23)**: date-range eksklusif; verifikasi nominal webhook; out-of-order;
plugin traversal/manifest/versi/transaksi; `license:validate` hilang; dev_bypass
default true; terminate fallback; end tanpa trx; giveNotice/renew tanpa guard;
refund parsial mengunci; markReceived/forfeit settled; kredit prorate terbalik;
transfer tanpa lock; settlement di luar trx; signature tanpa validasi; link sign
mati; duplikat meter; crash log WA null; PSEO fabrikasi; view var hilang;
notifikasi duplikat + tanpa endpoint tenant; command status filter salah;
README tanpa peringatan demo.

Semua diperbaiki + regression test. Detail per-file: `docs/FEATURE_MATRIX.md`.

---

## 8. Keamanan (OWASP)

BOLA/IDOR: scoping properti di semua endpoint sensitif + Filament
(`getEloquentQuery`) + halaman laporan; isolasi tenant penuh; policy mati tidak
dihapus (DEBT tercatat). AuthN/Z: Sanctum + panel guard + `canAccessPanel` +
`active.account` + throttle. Injection: validasi + ORM + `$fillable`; slug regex
+ containment untuk path filesystem. Webhook: signature + fail-closed + amount
check + idempotency. Secret: tak ada di repo; gateway di DB settings. Upload:
validasi MIME/size/magic-bytes (signature). Clickjacking/error leakage: debug
off di prod (runbook). Sisa manual: allowlist IP webhook, TrustProxies, drill
restore. (`docs/SECURITY_REVIEW.md`)

---

## 9. Pengujian — 354 passed, 0 failed, 815 assertions

| Area | File | N |
|---|---|---|
| Hardening admin API | `AdminSecurityHardeningTest` | 14 |
| Report endpoints | `ReportEndpointsTest` | 5 |
| Notifikasi lifecycle | `NotificationLifecycleTest` | 2 |
| Plugin/theme lifecycle | `PluginThemeLifecycleTest` | 4 |
| E-Contract + portal sign | `EContractTest` | 5 |
| PSEO routes | `PseoRoutesTest` | 4 |
| Print render PDF | `PrintRenderTest` | 2 |
| Reminder commands | `ReminderCommandsTest` | 2 |
| License command | `LicenseValidateCommandTest` | 3 |
| Deposit ledger | `DepositLedgerTest` + `DepositServiceTest` | 12 |
| MoveRoom | `MoveRoomServiceTest` | 4 |
| CheckInOut | `CheckInOutServiceTest` | 5 |
| Utility billing/rates | `UtilityBillingTest` + 2 service test | 14 |
| WhatsApp service | `WhatsAppServiceTest` | 5 |
| Portal/booking/landing/model/workflow + lain | suite existing | ±269 |

Perintah: `composer validate` ✓ · `optimize:clear` ✓ · `route:list` 257 ✓ ·
`migrate --pretend` ✓ · `pint --test` file baru ✓ · `php artisan test` ✓.
BLOCKED: browser/E2E manual, gateway/WA sungguhan, server lisensi asli.

---

## 10. UI/UX (16 perbaikan)

Popup pembelian ditulis ulang (bisa ditutup, fixed, tidak merusak layout);
footer dead-link diperbaiki; PDF invoice/kwitansi layout tabel (DomPDF);
view mati + link registrasi mati dihapus; baris tagihan portal bisa diklik;
form booking disembunyikan pasca-sukses; spacer sticky-bar mobile; aria-label/
aria-current/`prefers-reduced-motion`/noscript fallback; label demo dev-only.

---

## 11. Performance & Reliability

Eager loading di endpoint yang diubah; query massal `markAllRead`; scheduler 7
job + queue database; idempotency webhook + dedupe notifikasi; transaksi DB di
seluruh operasi multi-record kritis. `balance_after` ledger berpotensi drift
pada race ekstrem (terdokumentasi, non-kritis: saldo dihitung dari ledger).

---

## 12. Deployment Runbook Ringkas

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force        # jangan fresh/refresh
php artisan license:validate       # gagal = pairing via /__pair
php artisan db:seed --class=UserSeeder --force  # bila perlu akun awal
php artisan config:cache && route:cache && view:cache
```
Scheduler `* * * * * artisan schedule:run`; `queue:work` via supervisor.
Env prod: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` https,
`LICENSE_DEV_BYPASS=false`, SMTP asli, TrustProxies bila di balik proxy.
Backup harian 02:00 (`--only-db --keep=7`) — simpan off-server + drill restore.
(Penuh: `docs/DEPLOYMENT_RUNBOOK.md`)

---

## 13. Blocker Manual & Batasan

1. `LICENSE_DEV_BYPASS=false` + kredensial gateway/WA via Pengaturan.
2. Uji 1 transaksi sandbox end-to-end + replay webhook.
3. Drill restore backup 1×.
4. Scheduler + queue aktif.
5. `APP_DEBUG=false`, URL/SMTP asli.
6. Verifikasi visual manual di HP/tablet/desktop (no browser tooling).

---

## 14. Skor Final & Status

| Kategori | Bobot | Skor |
|---|---|---|
| Functional completeness | 25 | 24 |
| Business logic & data integrity | 20 | 19 |
| Security & authorization | 20 | 19 |
| Testing & regression | 15 | 15 |
| UI/UX & responsivitas | 10 | 9 |
| Performance, docs, ops | 10 | 10 |
| **Total** | **100** | **96** |

Status: **READY WITH LIMITATIONS** — siap staging/UAT; go-live setelah
§13 tuntas. Sisa 4 poin murni butuh tangan manusia, bukan kode.

---

## 15. Indeks Dokumen Terkait

- `docs/PROJECT_AUDIT.md` — baseline & stack
- `docs/FEATURE_MATRIX.md` — matriks per-modul + tindakan per file
- `docs/SECURITY_REVIEW.md` — 23 temuan sesi-2 + mitigasi
- `docs/TEST_REPORT.md` — 59 test baru + command + failure log
- `docs/PRODUCTION_READINESS.md` — skor & blocker
- `docs/DEPLOYMENT_RUNBOOK.md` — prosedur deploy/rollback/troubleshoot
- `README.md` — instalasi + peringatan kredensial demo
