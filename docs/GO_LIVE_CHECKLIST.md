# Go-Live Checklist (wajib sebelum serah terima ke pembeli)

## 1. Instalasi bersih (15 menit)

```bash
cp .env.example .env
# isi APP_URL https, DB_*, MAIL_*, LICENSE_DEV_BYPASS=false
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan koskosan:create-owner   # JANGAN db:seed (diblokir di production)
php artisan optimize
```

## 2. Amankan akun demo (5 menit)

```bash
php artisan koskosan:remove-demo        # nonaktifkan @kos.test
# atau: php artisan koskosan:remove-demo --delete --force
```

Verifikasi: login `admin@kos.test` harus **gagal**.

## 3. Kunci pihak ketiga (30 menit)

Ikuti `docs/PRODUCTION_KEYS.md`: gateway sandbox → transaksi uji
Rp 10.000 → webhook OK → baru ganti ke production.

## 4. Operasional harian (10 menit)

- Cron aktif: `* * * * * ... schedule:run` (lihat `DEPLOYMENT.md`).
- Supervisor: `queue:work` + `schedule:work` (atau cron).
- Backup: `php artisan backup:run --only-db` sukses, file tersimpan.
- HTTPS aktif, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.

## 5. Smoke test bisnis (20 menit)

1. Buat properti → tipe kamar → 1 kamar.
2. Buat penyewa + kontrak aktif → kamar jadi Terisi.
3. Generate invoice → bayar (manual/gateway) → Lunas.
4. Buat laporan maintenance → selesaikan.
5. Booking publik → setujui → jadi kontrak.
6. Cek dashboard `/admin`: KPI, Action Center, chart, tabel terisi.

## 6. Serah terima

- Ganti password semua akun, catat di password manager.
- Berikan pembeli: URL admin, akun owner, `docs/COMMERCIAL.md`
  (cakupan support), link Syarat & Ketentuan + Kebijakan Privasi.
- Simpan backup pertama di luar server.
