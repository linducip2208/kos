# REST API v1 — Panduan Flutter / Mobile

Base URL produksi: `https://kos.whitelabel.co.id/api`

Semua respons JSON. Sertakan header `Accept: application/json`.
Endpoint berlabel 🔒 butuh `Authorization: Bearer <token>` (Sanctum).
Login dibatasi 10x/menit per IP (`throttle:10,1`).

## Auth

| Method | Endpoint | Body | Respons |
|---|---|---|---|
| POST | `/v1/tenant/auth/login` | `phone`, `password` | `token`, `occupant{id,name,phone,email,whatsapp}` |
| POST | `/v1/admin/auth/login` | `email`, `password` | `token`, `user{...}` |
| POST | `/v1/tenant/auth/logout` 🔒 | — | `message` |
| GET | `/v1/tenant/auth/me` 🔒 | — | `occupant` |
| PUT | `/v1/tenant/profile` 🔒 | `name`, `phone` (unik), `email?`, `whatsapp?` | `message`, `occupant` |
| POST | `/v1/tenant/profile/password` 🔒 | `current_password`, `password` + `password_confirmation` (min 6) | `message` |

Contoh login:

```bash
curl -X POST https://kos.whitelabel.co.id/api/v1/tenant/auth/login \
  -H "Accept: application/json" \
  -d '{"phone":"081234000001","password":"password"}'
# → {"token":"...","occupant":{"id":1,"name":"Andi Prasetyo",...}}
```

## Publik (tanpa login)

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/v1/public/properties` | List properti aktif: `id, slug, name, address, city, province, description, facilities[], photos[] (URL absolut), total_rooms, available_rooms, booking_url` |
| GET | `/v1/public/properties/{slug\|id}` | Detail + `rooms[]` (kamar tersedia: nomor, lantai, ukuran, harga bulanan/tahunan, tipe + `type_photos[]`) |
| POST | `/v1/public/bookings/{slug\|id}` | Booking kamar. Body: `name`, `phone`, `desired_move_in` (> hari ini), `billing_cycle` (monthly/quarterly/yearly), opsional `email, whatsapp, room_type_id, room_id, message`. → 201 `{message, id}` |
| GET | `/v1/public/contract/{token}` | Data e-contract via token: status, lease, tanda tangan, `pdf_url`, `expires_at` |
| POST | `/v1/public/contract/{token}` + `/sign` | Tanda tangan: multipart `signature` (PNG/JPG ≤2MB) + `agree=1`. Token hangus setelah dipakai. |

## Penyewa 🔒 (`/v1/tenant/...`)

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/dashboard` | `lease{id,room_number,property,start_date,end_date,status}`, `unpaid_invoices`, `unpaid_total`, `open_maintenance` |
| GET | `/lease` | Kontrak aktif (404 bila tidak ada) |
| GET | `/invoices` | Tagihan sendiri, paginasi (`data,total,page,pages`) |
| GET | `/invoices/{id}` | Detail + rincian item |
| POST | `/invoices/{id}/pay` | Buat transaksi gateway aktif. → `{order_id, amount, payment_url?, manual?, bank_info?}`. 422 bila lunas/batal/draft |
| POST | `/invoices/{id}/proof` | Upload bukti transfer (multipart: `proof` gambar ≤2MB, `amount` ≥1000, opsional `method, reference, notes`). → 201, status `pending_verification` |
| GET | `/maintenance` | List laporan sendiri (paginasi) |
| POST | `/maintenance` | Buat laporan: `title`, `description`, opsional `priority`, `photos[]` (maks 4 gambar ≤2MB). Butuh kontrak aktif. → 201 `{message, id}` |
| GET | `/maintenance/{id}` | Detail milik sendiri (foto URL absolut). 404 milik orang lain |
| POST | `/maintenance/{id}/cancel` | Batalkan bila status `open`/`triage` |
| GET/… | `/notifications…` | `GET /`, `GET /unread-count`, `PATCH /{id}/read`, `POST /read-all`, `DELETE /{id}` |
| POST | `/devices` | Simpan FCM token: `{fcm_token}` |
| DELETE | `/devices` | Hapus FCM token |

## Admin 🔒 (`/v1/admin/...`, guard Sanctum + akun aktif)

CRUD penuh: `properties`, `rooms` (+`available`, `PATCH {room}/status`),
`occupants` (+`{id}/leases`), `leases` (+`expiring`, `terminate`, `{id}/invoices`),
`invoices` (+`mark-paid`), `users` (tanpa show).
Profil: `auth/me|logout`, `PUT auth/profile`, `POST auth/change-password`.
Dashboard: `dashboard/summary|upcoming|recent-activity` (Gate + scoping properti).
Laporan: `reports/occupancy|revenue|overdue|turnover` (param `date_from,date_to,property_id`).
Lainnya: `settings*`, `license/*`, `plugins*`, `themes*`, `notifications*`,
`GET export` (Excel) & `export/invoices/{id}/pdf`.

## Catatan integrasi

- `photos[]` properti/kamar/maintenance dikirim sebagai **URL absolut** — langsung pakai di `Image.network`.
- Foto bukti/maintenance diupload sebagai **multipart**, bukan base64.
- `payment_url` (Midtrans Snap / Tripay checkout) dibuka di WebView; status final via webhook server — polling `GET invoices/{id}` untuk status terkini.
- Daftarkan `fcm_token` setiap login agar siap terima push.
- Rute legacy tanpa `/v1` (`/api/tenant/...`, `/api/properties...`) tetap aktif untuk kompatibilitas.
