# Dashboard Redesign — Kos Manager Premium (FASE 3)

Tanggal: 23 November 2026
Scope: **UI/UX dashboard administrator saja**. Tidak ada perubahan schema,
migration, model/relasi, API/endpoint, business logic, auth/otorisasi,
lisensi, permission, maupun migrasi SaaS/multi-tenant.

## Ringkasan

Dashboard Filament 5 + Tailwind 4 diredesign menjadi premium
property-management SaaS: header greeting WIB, KPI cards dengan ikon,
Action Center alert cards, chart analytics dengan opsi modern, tabel
operasional dengan badge status yang benar, hierarki section 1–6,
sidebar shell yang dirapikan, responsif penuh, dan dark mode kontras.

## File yang diubah

| File | Perubahan (presentasi saja) |
|---|---|
| `app/Filament/Pages/Dashboard.php` | `getHeading()` greeting WIB + nama user; `getSubheading()` tanggal Indonesia + tagline. Filter form tidak berubah. |
| `app/Filament/Widgets/StatsOverviewWidget.php` | Heading/description section; `Stat::icon()` (sebelumnya `descriptionIcon`); grid responsif `default:1 / sm:2 / lg:3 / xl:4 / 2xl:5`. Data & query sama. |
| `app/Filament/Widgets/DashboardActionCenterWidget.php` | `columnSpan = full`. Query Gate + count sama. |
| `app/Filament/Widgets/RevenueTrendChart.php` | Tambah `use App\Models\Invoice` (**bug fix**: class dipakai tapi belum diimpor); agregasi bulan portabel per driver DB (konvensi `ReportController::monthExpression()`); `getDescription()` + `getOptions()` Chart.js; `columnSpan = 1`. |
| `app/Filament/Widgets/ReceivableAgingChart.php` | `getDescription()` + `getOptions()`; `columnSpan = 1`. |
| `app/Filament/Widgets/BookingFunnelChart.php` | Horizontal bar (`indexAxis: y`); `getDescription()` + `getOptions()`; `columnSpan = 1`. |
| `app/Filament/Widgets/MaintenanceOverviewChart.php` | `cutout 62%`; `getDescription()` + `getOptions()`; `columnSpan = 1`. |
| `app/Filament/Widgets/RoomStatusWidget.php` | `sort 2 → 7`; heading/description via `Table`; empty state; **bug fix**: closure `fn ($s)` → `fn ($state)` (Filament 5 resolve by-name; `$s` menerima `null` sehingga badge kosong). |
| `app/Filament/Widgets/RecentPaymentsWidget.php` | `sort 3 → 8`; `columnSpan = full`; heading/description; empty state; **bug fix** closure `$s` → `$state` (badge status kosong). |
| `app/Filament/Widgets/PendingVerificationsWidget.php` | `sort 4 → 9`; `columnSpan = full`; heading/description; empty state. |
| `resources/views/filament/widgets/dashboard-action-center.blade.php` | Rewrite premium: ikon per tone, label + hint urgensi, count badge, hover, empty state profesional. |
| `resources/css/filament/admin/theme.css` | Blok `FASE 3`: token `#2563EB/#1D4ED8/#F5F7FB/#FFFFFF/#172033/#647B8B/#E5EAF1`, KPI premium, action cards, chart/table polish, sidebar shell, responsif, dark mode, reduced-motion, focus-visible. |
| `tests/Feature/Filament/DashboardRedesignTest.php` | **Baru**: render header+filter, render 8 widget, role visibility, keunikan sort order. |
| `scripts/dashboard-screenshots.mjs` | **Baru**: tooling screenshot Playwright multi-viewport + cek overflow + console errors. |

## Hierarki layout (sort unik, tanpa konflik)

1. StatsOverview (1) → 2. ActionCenter (2) → 3. Revenue (3) + Receivable (4)
→ 4. Booking (5) + Maintenance (6) → 5. RoomStatus (7) → 6. RecentPayments (8)
→ 7. PendingVerifications (9). AccountWidget bawaan tetap tampil.

## Perbaikan bug yang ditemukan saat audit interaksi

1. `RevenueTrendChart` memakai `Invoice::` tanpa import → fatal saat
   `getData()` dipanggil. Ditambah `use App\Models\Invoice;`.
2. Query `DATE_FORMAT(...)` (MySQL-only) fatal di SQLite (test). Dibuat
   portabel per driver mengikuti konvensi `ReportController::monthExpression()`.
3. Badge kolom `status` di `RoomStatusWidget` & `RecentPaymentsWidget`
   kosong karena closure memakai parameter `$s`; Filament 5
   `evaluate()` resolve by-name sehingga `$s = null`. Diperbaiki ke `$state`
   (terverifikasi: "Tersedia", "Tunggakan", dst. tampil + berwarna).
4. Kontras heading kartu chart di dark mode tertimpa rule light-only.
   Ditambah override `.dark`.

## Hasil verifikasi

- `composer validate`: valid.
- `php artisan optimize:clear`: OK.
- `php artisan test`: **379 passed** (375 existing + 4 baru), 0 gagal.
- `vendor/bin/pint --test` (file dashboard): passed.
- `npm run build`: sukses (`theme-*.css` 635 KB, gzip 68 KB).
- Playwright (Chromium, login `admin@kos.test`):
  - 1920×1080, 1366×768, 768×1024, 390×844 → semua widget render,
    `documentElement.scrollWidth == innerWidth` (tanpa horizontal scroll),
    **tanpa console/page errors**.
  - Dark mode 1920 → kontras heading terverifikasi via clip screenshot.
  - Sidebar drawer mobile/tablet → terbuka dengan label + active state.
- Role visibility: maintenance tidak melihat chart finance
  (`canView() === false`), dashboard tetap 200 OK.

## Screenshot

- `docs/screenshots/dashboard-desktop-1920x1080.png`
- `docs/screenshots/dashboard-laptop-1366x768.png`
- `docs/screenshots/dashboard-tablet-768x1024.png`
- `docs/screenshots/dashboard-mobile-390x844.png`
- `docs/screenshots/dashboard-dark-1920x1080.png`
- `docs/screenshots/dashboard-tablet-768x1024-sidebar.png`
- `docs/screenshots/dashboard-mobile-390x844-sidebar.png`
- `docs/screenshots/console-errors.txt` ("no console/page errors")

## Catatan / sisa

- Nilai KPI/chart/tabel mengikuti data dev-DB apa adanya (mis. revenue
  6 bulan terakhir didominasi data seed lama) — bukan dummy, tidak diubah.
- Tidak ada dependency CDN/external baru; polling interval tidak berubah
  (60 dtk stats/action, 120 dtk chart) sehingga tidak ada request tambahan.
