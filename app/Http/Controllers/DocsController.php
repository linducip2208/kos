<?php

namespace App\Http\Controllers;

class DocsController extends Controller
{
    protected function demoAccounts(): array
    {
        return [
            ['role' => 'Super Admin', 'email' => 'admin@kos.test', 'password' => 'password', 'scope' => 'Akses penuh semua menu + pengaturan'],
            ['role' => 'Owner', 'email' => 'owner@kos.test', 'password' => 'password', 'scope' => 'Laporan, keuangan & monitoring usaha'],
            ['role' => 'Property Manager', 'email' => 'manager@kos.test', 'password' => 'password', 'scope' => 'Kelola operasional properti, kamar, penghuni'],
            ['role' => 'Auditor', 'email' => 'auditor@kos.test', 'password' => 'password', 'scope' => 'Lihat data dan audit log tanpa mengubah data'],
        ];
    }

    protected function tutorial(): array
    {
        return [
            [
                'phase' => 'Fase 1: Setup Awal',
                'steps' => [
                    ['num' => 1, 'title' => 'Tambah Properti Kos', 'action' => 'Buka menu Properti → Tambah Properti. Isi nama, alamat, dan deskripsi kos.', 'detail' => 'Properti adalah induk dari semua data. Satu properti bisa punya banyak tipe kamar dan kamar.'],
                    ['num' => 2, 'title' => 'Atur Tipe Kamar', 'action' => 'Buka menu Tipe Kamar → Tambah Tipe. Tentukan nama, fasilitas, dan harga dasar.', 'detail' => 'Tipe kamar menentukan harga default. Misal: Standard, Premium, VIP.'],
                    ['num' => 3, 'title' => 'Tambah Kamar', 'action' => 'Buka menu Kamar → Tambah Kamar. Pilih properti dan tipe, isi nomor kamar.', 'detail' => 'Setiap kamar punya status: tersedia, terisi, maintenance.'],
                ],
            ],
            [
                'phase' => 'Fase 2: Data Penghuni',
                'steps' => [
                    ['num' => 4, 'title' => 'Input Data Penghuni', 'action' => 'Buka menu Penghuni → Tambah Penghuni. Isi nama, telepon, dan data KTP.', 'detail' => 'Penghuni akan punya akun portal sendiri untuk lihat invoice.'],
                    ['num' => 5, 'title' => 'Buat Kontrak Sewa', 'action' => 'Buka menu Sewa → Tambah Sewa. Pilih penghuni, kamar, tanggal mulai & selesai.', 'detail' => 'Sewa menentukan periode tinggal dan nominal sewa per bulan.'],
                ],
            ],
            [
                'phase' => 'Fase 3: Keuangan',
                'steps' => [
                    ['num' => 6, 'title' => 'Generate Invoice', 'action' => 'Invoice otomatis dibuat bulanan. Atau buka Invoice → Buat Invoice manual.', 'detail' => 'Invoice mencakup sewa + listrik + air + biaya lain.'],
                    ['num' => 7, 'title' => 'Catat Pembayaran', 'action' => 'Buka Pembayaran → Tambah Pembayaran. Pilih invoice dan metode bayar.', 'detail' => 'Mendukung tunai, transfer bank, Midtrans, Tripay.'],
                    ['num' => 8, 'title' => 'Catat Tagihan Utilitas', 'action' => 'Buka Meteran → Tambah Pembacaan. Input angka meter listrik & air.', 'detail' => 'Tagihan utilitas otomatis masuk ke invoice bulan berikutnya.'],
                ],
            ],
            [
                'phase' => 'Fase 4: Operasional',
                'steps' => [
                    ['num' => 9, 'title' => 'Kelola Maintenance', 'action' => 'Buka Maintenance → Tambah Request. Catat kerusakan dan status perbaikan.', 'detail' => 'Penghuni juga bisa request maintenance dari portal.'],
                    ['num' => 10, 'title' => 'Checklist Kamar', 'action' => 'Buka Checklist Kamar → Tambah. Periksa kondisi kamar saat check-in/out.', 'detail' => 'Mencegah dispute kerusakan saat penghuni pindah.'],
                    ['num' => 11, 'title' => 'Online Booking', 'action' => 'Aktifkan fitur booking dari halaman landing properti.', 'detail' => 'Calon penghuni bisa booking kamar langsung dari website.'],
                ],
            ],
            [
                'phase' => 'Fase 5: Laporan',
                'steps' => [
                    ['num' => 12, 'title' => 'Laporan Keuangan', 'action' => 'Buka Laporan → Keuangan. Filter tanggal dan lihat ringkasan.', 'detail' => 'Lihat pendapatan, piutang, expense, dan profit.'],
                    ['num' => 13, 'title' => 'Laporan Okupansi', 'action' => 'Buka Laporan → Okupansi. Lihat tingkat keterisian per properti.', 'detail' => 'Download Excel untuk analisis lebih lanjut.'],
                ],
            ],
        ];
    }

    protected function features(): array
    {
        return [
            [
                'group' => 'Master Data',
                'title' => 'Manajemen Properti & Kamar',
                'description' => 'Kelola banyak properti kos dalam satu dashboard. Setiap properti punya tipe kamar, fasilitas, dan harga yang berbeda.',
                'bullets' => ['Multi-properti dalam satu akun', 'Tipe kamar dengan fasilitas kustom', 'Status kamar real-time (tersedia/terisi/maintenance)', 'Upload foto properti & kamar', 'Deskripsi dan alamat lengkap'],
                'screenshot' => 'property-list.svg',
                'path' => '/admin/properties',
            ],
            [
                'group' => 'Master Data',
                'title' => 'Data Penghuni Lengkap',
                'description' => 'Database penghuni dengan data KTP, kontak darurat, dan riwayat sewa. Setiap penghuni dapat akses portal mandiri.',
                'bullets' => ['Data KTP & identitas', 'Kontak darurat', 'Riwayat sewa per penghuni', 'Portal login mandiri', 'Upload dokumen pendukung'],
                'screenshot' => 'occupant-list.svg',
                'path' => '/admin/occupants',
            ],
            [
                'group' => 'Transaksi',
                'title' => 'Sewa & Kontrak Otomatis',
                'description' => 'Atur periode sewa dengan tanggal mulai dan selesai. Sistem otomatis generate invoice setiap bulan sesuai kontrak.',
                'bullets' => ['Periode sewa fleksibel (harian/mingguan/bulanan/tahunan)', 'Auto-generate invoice bulanan', 'Perpanjangan kontrak otomatis', 'E-Contract dengan tanda tangan digital', 'Notifikasi H-30 sebelum kontrak habis'],
                'screenshot' => 'lease-list.svg',
                'path' => '/admin/leases',
            ],
            [
                'group' => 'Keuangan',
                'title' => 'Invoice & Pembayaran',
                'description' => 'Invoice otomatis dengan rincian sewa + utilitas + biaya lain. Pembayaran via tunai, transfer, atau payment gateway.',
                'bullets' => ['Invoice otomatis per bulan', 'Rincian: sewa + listrik + air + lain-lain', 'Payment gateway Midtrans & Tripay', 'Status pembayaran real-time (lunas/sebagian/belum)', 'Cetak PDF invoice & kwitansi'],
                'screenshot' => 'invoice-list.svg',
                'path' => '/admin/invoices',
            ],
            [
                'group' => 'Keuangan',
                'title' => 'Deposit & Ledger',
                'description' => 'Kelola uang deposit penghuni dengan ledger transparan. Setiap potongan dan pengembalian tercatat rapi.',
                'bullets' => ['Deposit per kontrak sewa', 'Ledger mutasi (masuk/keluar)', 'Potongan kerusakan otomatis', 'Pengembalian deposit', 'Riwayat lengkap per penghuni'],
                'screenshot' => 'deposit-list.svg',
                'path' => '/admin/deposits',
            ],
            [
                'group' => 'Operasional',
                'title' => 'Maintenance & Perbaikan',
                'description' => 'Catat dan lacak semua permintaan perbaikan. Penghuni bisa submit request maintenance langsung dari portal.',
                'bullets' => ['Request maintenance dari admin & penghuni', 'Prioritas (rendah/sedang/tinggi/urgent)', 'Tracking status perbaikan', 'Biaya perbaikan + vendor', 'History maintenance per kamar'],
                'screenshot' => 'maintenance-list.svg',
                'path' => '/admin/maintenance-requests',
            ],
            [
                'group' => 'Operasional',
                'title' => 'Meteran Listrik & Air',
                'description' => 'Catat angka meter tiap bulan. Tagihan utilitas otomatis dihitung dari selisih pemakaian × tarif.',
                'bullets' => ['Pembacaan meter per kamar', 'Tarif per properti (listrik/air)', 'Otomatis masuk invoice', 'Riwayat pemakaian', 'Deteksi pemakaian abnormal'],
                'screenshot' => 'meter-list.svg',
                'path' => '/admin/utility-readings',
            ],
            [
                'group' => 'Operasional',
                'title' => 'Booking Online & E-Contract',
                'description' => 'Calon penghuni booking dari landing page publik. Setelah disetujui, kontrak digital ditandatangani online.',
                'bullets' => ['Form booking publik per properti', 'Approval manager → jadi lease', 'E-Contract + tanda tangan digital', 'Checklist check-in/out kamar', 'Pindah kamar (room transfer)'],
                'screenshot' => 'booking-list.svg',
                'path' => '/admin/booking-requests',
            ],
            [
                'group' => 'Laporan',
                'title' => 'Laporan Keuangan Lengkap',
                'description' => 'Dashboard laporan dengan chart interaktif. Filter tanggal, properti, dan download PDF/Excel.',
                'bullets' => ['Ringkasan pendapatan & piutang', 'Chart revenue bulanan/tahunan', 'Laporan P&L (Profit & Loss)', 'Filter per properti', 'Export PDF & Excel'],
                'screenshot' => 'financial-report.svg',
                'path' => '/admin/financial-report',
            ],
            [
                'group' => 'Laporan',
                'title' => 'Okupansi & Analitik',
                'description' => 'Pantau tingkat keterisian per properti, kamar kosong, dan tren hunian dari dashboard operasional.',
                'bullets' => ['Tingkat hunian per properti', 'Daftar kamar kosong siap jual', 'Kontrak hampir habis (H-30)', 'Invoice jatuh tempo', 'Export Excel okupansi'],
                'screenshot' => 'occupancy-report.svg',
                'path' => '/admin',
            ],
            [
                'group' => 'Notifikasi',
                'title' => 'WhatsApp Blast & Reminder',
                'description' => 'Kirim tagihan, pengingat jatuh tempo, dan pengumuman massal via WhatsApp otomatis.',
                'bullets' => ['Reminder H-3 otomatis', 'Notice keterlambatan + denda', 'Blast pengumuman ke semua penghuni', 'Template pesan kustom', 'Log status terkirim/gagal'],
                'screenshot' => 'whatsapp-blast.svg',
                'path' => '/admin/whatsapp-blast',
            ],
            [
                'group' => 'Portal Penyewa',
                'title' => 'Portal & Mobile API',
                'description' => 'Penghuni mandiri: lihat tagihan, bayar online, lapor kerusakan, dan update profil tanpa hubungi admin.',
                'bullets' => ['Login portal penyewa', 'Dashboard tagihan & kontrak', 'Bayar via Midtrans/Tripay', 'Lapor maintenance + foto', 'REST API untuk aplikasi mobile'],
                'screenshot' => 'portal-dashboard.svg',
                'path' => '/portal',
            ],
        ];
    }

    public function index()
    {
        $baseUrl = rtrim(config('app.url'), '/');
        // Ikut host aktual saat diakses via domain produksi
        if (request()->getHost() !== parse_url($baseUrl, PHP_URL_HOST)) {
            $baseUrl = request()->getSchemeAndHttpHost();
        }

        $seo = [
            'title' => 'Dokumentasi — ' . setting('app_name', 'Kos Manager'),
            'description' => 'Panduan lengkap penggunaan aplikasi ' . setting('app_name', 'Kos Manager') . '. Tutorial step-by-step, fitur lengkap, dan akun demo.',
            'canonical' => $baseUrl . '/docs',
        ];

        return view('pseo.docs-index', [
            'seo' => $seo,
            'baseUrl' => $baseUrl,
            'adminUrl' => $baseUrl . '/admin',
            'portalUrl' => $baseUrl . '/portal',
            'apiUrl' => $baseUrl . '/api',
            'whatsapp' => setting('owner_phone', '', 'general') ?: '6281296052010',
            'demoAccounts' => $this->demoAccounts(),
            'tutorial' => $this->tutorial(),
            'features' => $this->features(),
        ]);
    }
}
