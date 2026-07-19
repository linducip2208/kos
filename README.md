# Kos Manager

Sistem manajemen kos berbasis web dibangun dengan **Laravel 13** dan **Filament PHP v5**. Mencakup manajemen properti, penyewa, kontrak, tagihan, pemeliharaan, hingga portal penyewa dan mobile API.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.3 · Laravel 13.6 |
| Admin Panel | Filament PHP v5.6 |
| Database | MySQL 8 |
| Auth API | Laravel Sanctum |
| Frontend Portal | Blade + Livewire |
| Queue/Session/Cache | Database driver |

---

## Fitur

### Admin Panel (`/admin`)

| Menu | Deskripsi |
|---|---|
| **Properti** | Manajemen gedung/lokasi kos |
| **Tipe Kamar** | Konfigurasi tipe kamar dan harga dasar |
| **Kamar** | Status kamar (tersedia / terisi / pemeliharaan) |
| **Penyewa** | Data penyewa + akses portal |
| **Kontrak Sewa** | Manajemen masa sewa |
| **Tagihan** | Invoice bulanan + status pembayaran |
| **Pemeliharaan** | Laporan & progres kerusakan |
| **Pembacaan Meter** | Catat listrik/air per kamar |
| **Booking Online** | Permintaan booking dari calon penyewa |
| **Kontrak Digital** | E-contract + tanda tangan digital |
| **Cek Kamar** | Checklist kondisi kamar (check-in / check-out) |
| **Laporan Keuangan** | Rekapitulasi pendapatan & tingkat hunian |
| **WhatsApp Blast** | Kirim notifikasi massal ke penyewa |
| **Pengaturan** | General settings & payment gateway |

### Portal Penyewa (`/portal`)

| Halaman | Deskripsi |
|---|---|
| Login | Autentikasi dengan email + password portal |
| Dashboard | Ringkasan tagihan, kontrak, laporan pemeliharaan |
| Tagihan | Daftar & detail invoice |
| Pemeliharaan | Lihat & buat laporan kerusakan |
| Profil | Update data pribadi & ganti password |

### Mobile API (`/api`)

| Endpoint | Method | Keterangan |
|---|---|---|
| `/api/auth/login` | POST | Login penyewa, mendapat token |
| `/api/auth/logout` | POST | Logout (hapus token) |
| `/api/tenant/me` | GET | Data profil penyewa |
| `/api/tenant/dashboard` | GET | Ringkasan dashboard |
| `/api/tenant/lease` | GET | Detail kontrak aktif |
| `/api/tenant/invoices` | GET | Daftar tagihan |
| `/api/tenant/invoices/{id}` | GET | Detail tagihan |
| `/api/tenant/maintenance` | GET | Daftar laporan pemeliharaan |
| `/api/tenant/maintenance` | POST | Buat laporan baru |
| `/api/properties` | GET | Daftar properti (publik) |
| `/api/properties/{id}` | GET | Detail properti (publik) |

### Online Booking (`/booking/{property}`)

Form booking kamar untuk calon penyewa — publik, tanpa login.

---

## Persyaratan

- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Node.js >= 18 (untuk build asset)

---

## Instalasi

```bash
# 1. Clone / ekstrak project
cd kos_app

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies & build
npm install && npm run build

# 4. Salin konfigurasi environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

### Konfigurasi Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koskosan
DB_USERNAME=root
DB_PASSWORD=
```

Buat database di MySQL:

```sql
CREATE DATABASE koskosan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Migrasi & Seeder

```bash
# Jalankan migrasi
php artisan migrate

# Isi data contoh
php artisan db:seed
```

> **Catatan:** Jika database kosong (pertama kali setup), sementara ubah `CACHE_STORE=file` di `.env`, jalankan migrate, kemudian kembalikan ke `CACHE_STORE=database`.

### Jalankan Server

```bash
php artisan serve
```

Akses di: `http://localhost:8000`

---

## Akun Login

### Admin Panel — `http://localhost:8000/admin`

| Field | Value |
|---|---|
| Email | `admin@kos.test` |
| Password | `password` |

### Portal Penyewa — `http://localhost:8000/portal/login`

| Nama | Email | Password |
|---|---|---|
| Andi Prasetyo | `andi@gmail.com` | `password` |
| Siti Nurhaliza | `siti.nur@yahoo.com` | `password` |
| Budi Santoso | `budi.s@gmail.com` | `password` |
| Dewi Lestari | `dewi.lestari@gmail.com` | `password` |
| Rizky Firmansyah | `rizky.f@hotmail.com` | `password` |
| Maya Anggraini | `maya.anggraini@gmail.com` | `password` |
| Hendra Gunawan | `hendra.g@gmail.com` | `password` |

### Mobile API

```bash
# Login — dapatkan token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"andi@gmail.com","password":"password"}'

# Gunakan token di header
curl http://localhost:8000/api/tenant/dashboard \
  -H "Authorization: Bearer {token}"
```

---

## Data Contoh (Seeder)

Setelah `db:seed`, database berisi:

| Data | Jumlah | Keterangan |
|---|---|---|
| Properti | 2 | Kos Bahagia Pusat & Selatan (Bandung) |
| Kamar | 15 | Berbagai tipe & status |
| Penyewa | 7 | Semua aktif dengan akses portal |
| Kontrak Sewa | 8 | 7 aktif, 1 expired |
| Tagihan | 28 | 3 bulan mundur + bulan berjalan |
| Kontrak Digital | 4 | Status: fully_signed, owner_signed, sent, draft |
| Checklist Kamar | 4 | 3 check-in, 1 check-out dengan kerusakan |
| Laporan Pemeliharaan | 7 | Berbagai status & prioritas |
| Booking Request | 5 | Berbagai status |

---

## Testing

Test suite menggunakan **SQLite in-memory** (terpisah dari database development MySQL).

```bash
# Jalankan semua test
php artisan test

# Dengan detail per test
php artisan test --verbose

# Filter group tertentu
php artisan test --filter=PortalTest
php artisan test --filter=ApiTest
php artisan test --filter=BookingTest
```

### Cakupan Test

| Direktori | Cakupan |
|---|---|
| `Unit/Models/` | Property, Room, Occupant, Lease, Invoice, MaintenanceRequest |
| `Unit/Services/` | InvoiceService, EContractService, WhatsAppService |
| `Feature/Portal/` | Login, dashboard, invoice, maintenance, profil |
| `Feature/Api/` | Auth login/logout, dashboard, lease, invoice, maintenance |
| `Feature/` | BookingTest, FinancialReport, RoomChecklist, Commands |

---

## Struktur Direktori

```
app/
├── Filament/
│   ├── Pages/          — FinancialReport, WhatsAppBlast, GeneralSettings
│   ├── Resources/      — 11 resource Filament (CRUD)
│   └── Widgets/        — StatsOverview, RoomStatus, RecentPayments
├── Http/
│   ├── Controllers/
│   │   ├── Api/        — Mobile API controllers
│   │   └── Portal/     — Portal penyewa controllers
│   └── Middleware/     — PortalAuth
├── Models/             — 12 Eloquent models
└── Services/           — InvoiceService, EContractService, WhatsAppService

database/
├── migrations/         — 23 migration files
├── seeders/            — 11 seeders
└── factories/          — 8 factories

routes/
├── web.php             — Portal penyewa + booking + webhook
└── api.php             — Mobile API endpoints

tests/
├── Unit/
└── Feature/
```

---

## Perintah Artisan Berguna

```bash
# Reset total + isi ulang data
php artisan migrate:fresh --seed

# Bersihkan cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Jalankan queue worker
php artisan queue:work

# Kirim pengingat invoice jatuh tempo (bisa dijadwal cron)
php artisan invoices:send-reminders
```

---

## Environment Penting

| Variabel | Default | Keterangan |
|---|---|---|
| `SESSION_DRIVER` | `database` | Session disimpan di tabel `sessions` |
| `QUEUE_CONNECTION` | `database` | Queue pakai tabel `jobs` |
| `CACHE_STORE` | `database` | Cache pakai tabel `cache` |
| `MAIL_MAILER` | `log` | Email ditulis ke `storage/logs` (dev) |

---

## Lisensi

Untuk penggunaan pribadi / internal.
