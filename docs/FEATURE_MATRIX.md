# Feature Matrix — KOS Management

Legenda status: OK = terverifikasi + ada test · FIXED = diperbaiki sesi ini · GAP = belum ada test · DEBT = tech debt tercatat.

## Admin API (`/api/v1/admin`, `auth:sanctum` + `active.account`)

| Modul | Route | Backend | UI | Test | Status |
|---|---|---|---|---|---|
| Auth admin | `auth/login|logout|me|profile|change-password` | `Api\Admin\AuthController` | `UserResource` | Parsial | GAP (login rate-limit baru, belum ada test khusus) |
| Dashboard | `dashboard/summary|upcoming|recent-activity` | `Api\Admin\DashboardController` + `DashboardMetricsService` | Pages/Dashboard + widgets | `AdminSecurityHardeningTest` | FIXED (Gate `dashboard.view` + scoping properti) |
| Properties | `properties*` | `PropertyController` | `PropertyResource` | `AdminAuthorizationTest` + hardening | OK |
| Rooms | `rooms*`, `rooms/available`, `rooms/{room}/status` | `RoomController` + `RoomStatusService` | `RoomResource` | hardening (scope) | OK |
| Occupants | `occupants*`, `occupants/{id}/leases` | `OccupantController` | `OccupantResource` | `AdminAuthorizationTest` | OK |
| Leases | `leases*`, `expiring`, `terminate`, `invoices` | `LeaseController` + `LeaseWorkflowService` | `LeaseResource` + checkin/transfer | hardening (`expiring_soon` + range) | FIXED |
| Invoices/Payment | `invoices*`, `mark-paid` | `InvoiceController` + `PaymentService` | `InvoiceResource`, `InvoicePaymentResource` | model/workflow + `PrintAuthorizationTest` | OK |
| Users | `users` (tanpa show) | `UserController` | `UserResource` | hardening (`index` + `ownerOnly`) | FIXED |
| Settings | `settings*` | `SettingController` | `GeneralSettings`, `PaymentGatewaySettings` | `SettingTest` | OK |
| License | `license*`, `/__pair/*` | `LicenseController`, `PairController` | `LicenseSettings` | mock di TestCase | GAP (butuh server lisensi asli) |
| Reports | `reports/occupancy|revenue|overdue|turnover` | `ReportController` | 3 report pages | parsial | GAP (revenue/overdue/turnover) |
| Plugin/Theme | `plugins*`, `themes*` | Managers | 2 management pages | otorisasi saja | GAP (install/activate) |
| Notifications | `notifications*` | `NotificationController` | — | — | GAP |
| Export/Print | `admin/export*`, web `print/*` | `ExportController`, `PrintController` + 2 Export class | — | hardening (scope) + print auth | FIXED |

## Portal penyewa + Tenant API + Publik

| Modul | Route | Backend | Test | Status |
|---|---|---|---|---|
| Portal web login/dashboard/invoice/maintenance/profil | `portal/*` (`portal.auth`) | `Portal\*Controller` | `PortalTest`, `Portal/*Test`, `RouteSecurityTest` | OK (+ `PortalAuth` kini cabut sesi nonaktif) |
| Tenant API auth/dashboard/lease/invoices/maintenance | `v1/tenant/*` + legacy | `Api\*Controller` (tenant) | `TenantApiTest`, `AuthApiTest` | OK (+ `active.account` middleware) |
| Landing/properti/booking/contact | `/`, `/property/*`, `/booking/*` | `LandingController`, `BookingController` | `LandingTest`, `BookingTest`, `FullFlowTest` | OK |
| Webhook Midtrans/Tripay | `/webhook/*` | `PaymentWebhookController` + `PaymentGatewayService` | hardening (fail-closed + idempotency) | FIXED |
| Blog/docs/sitemap/PSEO | `blog*`, `docs`, `sitemap.xml`, pseo | 4 controller | `LandingTest` (parsial) | GAP (15+ route PSEO tanpa test) |

## Filament-only (tanpa route API)

| Modul | Service | Test | Status |
|---|---|---|---|
| Deposit + ledger | `DepositService` | `DepositServiceTest` (parsial: refund/forfeit belum) | OK/GAP |
| Utilitas + tarif tiered | `UtilityService` + `UtilityRateService` | `UtilityServiceTest`, `UtilityRateServiceTest` (baru) | FIXED (tiered rate kini live) |
| EContract/check-in-out/pindah kamar | `EContractService`, `CheckInOutService`, `MoveRoomService` | — | GAP |
| WhatsApp blast | `WhatsAppService` | `WhatsAppBlastPageTest` (akses) | FIXED (halaman fungsional) |
| Expense/vendor/fasilitas/inventaris/CRM | model langsung | — | GAP |

## Tindakan sesi ini (file penting)

- `Api\Admin\DashboardController.php` — Gate + scoping + range fix.
- `Api\Admin\UserController.php` — `index()` wajib `ownerOnly`.
- `Api\Admin\ExportController.php`, `Exports/InvoicesExport.php`, `Exports/OccupancyExport.php` — scoping + 403 + bug `propertyIds()`.
- `PaymentWebhookController.php` — fail-closed + idempotency.
- `Api\Admin\LeaseController.php` — `expiring()` status set + range fix.
- `Models\User.php` — `canAccessPanel` + `FilamentUser`.
- `Middleware/EnsureActiveAccount.php` (baru), `Middleware/PortalAuth.php` — cabut akun nonaktif.
- `routes/api.php`, `routes/web.php` — `active.account` + `throttle:10,1` login.
- `Filament/Pages/WhatsAppBlast.php` + view + `$slug` — halaman fungsional.
- `Services/UtilityService.php`, `Services/UtilityRateService.php` — tiered rate live, hapus import mati.
- `tests/Feature/Api/AdminSecurityHardeningTest.php` (11 test), `tests/Feature/WhatsAppBlastPageTest.php`, `tests/Unit/Services/UtilityRateServiceTest.php`.
