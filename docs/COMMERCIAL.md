# Paket Komersial Kos Manager

Halaman harga publik: `/beli-aplikasi-kos` (Free Rp 0 / Growth Rp 2,5jt
one-time / Whitelabel Rp 10jt one-time). Dokumen ini adalah acuan internal
saat closing dengan pembeli.

## Yang didapat pembeli (semua paket berbayar)

- Source code + 51 migration, panduan `DEPLOYMENT.md`.
- Lisensi pairing v3 (aktivasi via `/__pair`).
- Instalasi 1x dibantu tim (vps fresh, sesuai `GO_LIVE_CHECKLIST.md`).
- Update bug-fix selama masa support via `git pull` + `migrate --force`.

## Batasan support (tulis di invoice penjualan)

| Termasuk | Tidak termasuk |
|---|---|
| Instalasi + pairing lisensi | Kustom fitur baru |
| Bug fitur bawaan selama masa support | Migrasi data dari sistem lain |
| Panduan kunci gateway/WA (`PRODUCTION_KEYS.md`) | Integrasi di luar daftar resmi |
| 1x training online (Growth/Whitelabel) | Maintenance server milik pembeli |

Masa support yang disarankan: Growth 30 hari, Whitelabel 90 hari +
prioritas. Setelah itu tawarkan paket maintenance terpisah.

## Kebijakan refund

Refund penuh bila aplikasi tidak dapat diinstal pada environment yang
memenuhi syarat dan tim gagal memperbaikinya dalam 14 hari kerja.
Syarat & ketentuan mengikat: `/syarat-ketentuan`,
`/kebijakan-privasi`.

## Yang TIDAK dijanjikan

- Multi-tenant SaaS (paket Whitelabel mencantumkan "Multi-Tenant":
  yang tersedia saat ini adalah instalasi terpisah per pembeli +
  plugin/theme engine — jelaskan ini saat closing agar tidak overclaim).
- Uptime server pembeli, deliverability WhatsApp/gateway pihak ketiga.
