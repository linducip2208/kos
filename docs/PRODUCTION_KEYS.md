# Panduan Kunci Pihak Ketiga (Production)

Semua kredensial integrasi disimpan di **database via panel admin**
(tipe `encrypted` untuk secret), bukan di `.env`. Artinya: file `.env`
pembeli tidak pernah berisi API key — cukup atur lewat UI.

## 1. Payment Gateway — `/admin/payment-gateway-settings`

| Field | Sandbox | Production |
|---|---|---|
| Provider aktif | `manual` (transfer bank) | `midtrans` / `tripay` |
| Midtrans | Server Key + Client Key **Sandbox** dari dashboard.midtrans.com → sandbox | Ganti dengan key **production**, nyalakan toggle **Mode Production** |
| Tripay | API Key + Private Key + Merchant Code **sandbox** dari merchant.tripay.co.id | Ganti dengan key production, nyalakan **Mode Production** |
| Webhook | Daftarkan URL `https://domain/pembayaran/callback` di dashboard provider | Sama, versi production |
| Batas IP (opsional) | Kosongkan saat uji | Isi `WEBHOOK_ALLOWED_IPS` di `.env` dengan IP provider |

Alur uji wajib: buat invoice uji Rp 10.000 → bayar via Snap/kanal Tripay →
pastikan status invoice jadi Lunas dan webhook tercatat tanpa error
(`storage/logs/laravel.log` bersih).

Mode `manual` (transfer + upload bukti + verifikasi cashier) selalu
tersedia sebagai fallback bila gateway belum dikonfigurasi.

## 2. WhatsApp — `/admin/whatsapp-blast` + `/admin/general-settings`

- Aktifkan toggle `whatsapp_enabled` (grup setting `notif`).
- Isi `whatsapp_api_url` (default ChatGo), `whatsapp_api_key`, `whatsapp_sender`.
- Kirim blast ke **nomor sendiri dulu** sebelum blast massal.
- Reminder otomatis (`invoices:send-reminders` jam 08:00) memakai konfigurasi
  yang sama; nonaktifkan bila belum siap.

## 3. Push Notification / FCM

- Pendaftaran device via `POST /api/v1/tenant/devices` (token FCM dari aplikasi mobile).
- Tanpa aplikasi mobile, fitur ini idle dan aman diabaikan.

## 4. Email — `.env`

- Dev: `MAIL_MAILER=log`. Production: ganti ke `smtp` + kredensial asli,
  `MAIL_FROM_ADDRESS` domain sendiri agar tidak masuk spam.

## 5. Lisensi — `.env`

- `LICENSE_SERVER_URL=https://whitelabel.co.id`, `LICENSE_DEV_BYPASS=false`
  di production. Pairing via wizard `/__pair`.
