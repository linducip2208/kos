<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['name' => 'Tips Kos', 'slug' => 'tips-kos', 'description' => 'Tips dan trik seputar dunia kos'],
            ['name' => 'Manajemen Properti', 'slug' => 'manajemen-properti', 'description' => 'Cara mengelola properti kos secara efektif'],
            ['name' => 'Keuangan', 'slug' => 'keuangan', 'description' => 'Tips keuangan untuk pemilik kos dan penghuni'],
            ['name' => 'Teknologi', 'slug' => 'teknologi', 'description' => 'Teknologi terbaru untuk manajemen kos modern'],
        ];

        foreach ($kategori as $cat) {
            BlogCategory::create($cat);
        }

        $artikel = [
            [
                'title' => '5 Tips Memilih Kos yang Nyaman untuk Mahasiswa',
                'category_id' => 1,
                'excerpt' => 'Memilih kos yang tepat adalah langkah penting bagi mahasiswa. Simak 5 tips berikut agar kamu tidak salah pilih hunian selama kuliah.',
                'content' => '<h2>Kenapa Memilih Kos Itu Penting?</h2><p>Kos bukan sekadar tempat tidur. Ini adalah rumah kedua selama kamu menempuh pendidikan. Kos yang nyaman akan mendukung produktivitas dan kesehatan mental.</p><h2>1. Lokasi Strategis</h2><p>Pilih kos yang dekat dengan kampus. Idealnya dalam radius 1-2 km agar kamu bisa berjalan kaki atau naik kendaraan umum dengan mudah.</p><h2>2. Fasilitas Lengkap</h2><p>Pastikan kos memiliki fasilitas dasar: kamar mandi bersih, dapur, area parkir, dan akses WiFi. Fasilitas tambahan seperti AC dan laundry adalah nilai plus.</p><h2>3. Keamanan Terjamin</h2><p>Cek apakah kos memiliki satpam, CCTV, atau setidaknya kunci gerbang. Jangan ragu bertanya tentang track record keamanan ke penghuni lama.</p><h2>4. Budget Sesuai</h2><p>Tentukan anggaran sebelum mencari kos. Jangan lupa hitung biaya tambahan seperti listrik, air, iuran kebersihan. Idealnya kos tidak lebih dari 30% uang saku bulanan.</p><h2>5. Cek Lingkungan Sekitar</h2><p>Kelilingi area kos di siang dan malam hari. Cek kebisingan, akses ke warung makan, laundry, dan transportasi umum.</p><h2>Kesimpulan</h2><p>Jangan terburu-buru. Survei minimal 3-4 tempat, bandingkan, dan pilih yang paling sesuai kebutuhanmu.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(5),
                'meta_title' => '5 Tips Memilih Kos yang Nyaman untuk Mahasiswa — Blog Kos Manager',
                'meta_description' => 'Bingung cari kos untuk kuliah? Simak 5 tips memilih kos nyaman: lokasi strategis, fasilitas lengkap, keamanan, budget, dan lingkungan.',
            ],
            [
                'title' => 'Cara Mengelola Keuangan Kos dengan Aplikasi Kos Manager',
                'category_id' => 3,
                'excerpt' => 'Kelola keuangan kos jadi lebih mudah dengan aplikasi. Pelajari cara mencatat pemasukan, pengeluaran, dan tagihan otomatis.',
                'content' => '<h2>Masalah Umum Pemilik Kos</h2><p>Banyak pemilik kos masih menggunakan Excel atau catatan manual. Ini rawan error, sulit dilacak, dan memakan waktu.</p><h2>Fitur Keuangan Kos Manager</h2><p>Aplikasi Kos Manager menyediakan:</p><ul><li>Invoice otomatis setiap bulan — sistem generate tagihan berdasarkan kontrak sewa</li><li>Pencatatan pembayaran — lunas, sebagian, atau belum bayar</li><li>Tagihan utilitas — listrik dan air tercatat per kamar</li><li>Laporan keuangan — ringkasan bulanan dengan grafik interaktif</li></ul><h2>Cara Mulai</h2><p>1. Tambahkan properti kos kamu<br>2. Input data kamar dan penghuni<br>3. Buat kontrak sewa (tanggal mulai & selesai)<br>4. Biarkan sistem generate invoice otomatis</p><h2>Keuntungan</h2><p>Dengan sistem otomatis, kamu bisa:<ul><li>Hemat waktu 70% untuk administrasi</li><li>Pantau pembayaran real-time</li><li>Cetak laporan untuk SPT pajak</li><li>Notifikasi WhatsApp ke penghuni</li></ul></p>',
                'is_published' => true,
                'published_at' => now()->subDays(3),
                'meta_title' => 'Cara Mengelola Keuangan Kos dengan Aplikasi — Kos Manager',
                'meta_description' => 'Tinggalkan Excel! Kelola keuangan kos dengan aplikasi Kos Manager. Invoice otomatis, laporan real-time, notifikasi WhatsApp.',
            ],
            [
                'title' => 'Investasi Kos: Modal Kecil, Cuan Panjang',
                'category_id' => 2,
                'excerpt' => 'Bisnis kos termasuk investasi properti paling stabil. Pelajari cara memulai bisnis kos dengan modal terjangkau.',
                'content' => '<h2>Kenapa Bisnis Kos Menguntungkan?</h2><p>Setiap tahun, jutaan mahasiswa dan pekerja rantau mencari tempat tinggal. Permintaan kos selalu tinggi, terutama di kota pendidikan dan industri.</p><h2>Modal Awal</h2><p>Kamu bisa mulai dari:<ul><li>Kontrak rumah lalu disewakan per kamar (kos-kosan kontrak)</li><li>Renovasi rumah sendiri menjadi kos</li><li>Bangun baru di lahan strategis</li></ul></p><h2>Estimasi Balik Modal</h2><p>Dengan 10 kamar @ Rp 800.000/bulan = Rp 8.000.000/bulan. Setelah dipotong biaya operasional 20%, nett sekitar Rp 6.400.000/bulan. Balik modal dalam 2-3 tahun.</p><h2>Tips Sukses</h2><p>1. Pilih lokasi dekat kampus atau kawasan industri<br>2. Berikan fasilitas yang membedakan dari kompetitor<br>3. Gunakan aplikasi manajemen untuk efisiensi<br>4. Bangun reputasi baik — review positif dari penghuni</p>',
                'is_published' => true,
                'published_at' => now()->subDays(1),
                'meta_title' => 'Investasi Kos: Modal Kecil, Cuan Panjang — Blog Kos Manager',
                'meta_description' => 'Tertarik bisnis kos? Pelajari cara memulai investasi kos dengan modal terjangkau. Estimasi balik modal 2-3 tahun.',
            ],
            [
                'title' => 'Checklist Bulanan Pemilik Kos yang Wajib Dilakukan',
                'category_id' => 2,
                'excerpt' => 'Jangan sampai kos terbengkalai. Ikuti checklist bulanan ini untuk menjaga properti tetap prima dan penghuni betah.',
                'content' => '<h2>Kenapa Checklist Penting?</h2><p>Perawatan rutin mencegah kerusakan besar dan menjaga nilai properti. Penghuni juga lebih betah kalau kos terawat.</p><h2>Checklist Bulanan</h2><ol><li><strong>Cek kebersihan area umum</strong> — koridor, dapur bersama, area parkir</li><li><strong>Periksa instalasi listrik & air</strong> — stop kontak longgar, pipa bocor</li><li><strong>Cek atap & talang</strong> — terutama musim hujan</li><li><strong>Semprot anti hama</strong> — kecoa, tikus, rayap</li><li><strong>Catat meteran listrik & air</strong> — untuk tagihan bulanan</li><li><strong>Evaluasi keluhan penghuni</strong> — maintenance request yang belum selesai</li></ol><h2>Gunakan Fitur Maintenance Kos Manager</h2><p>Dengan fitur maintenance request, penghuni bisa submit keluhan langsung dari portal. Kamu bisa tracking status perbaikan dan biayanya.</p>',
                'is_published' => true,
                'published_at' => now()->subDays(2),
                'meta_title' => 'Checklist Bulanan Pemilik Kos — Blog Kos Manager',
                'meta_description' => 'Checklist bulanan wajib untuk pemilik kos: cek kebersihan, listrik, air, hama, meteran, dan keluhan penghuni.',
            ],
            [
                'title' => 'Keamanan Kos: 7 Langkah Lindungi Penghuni dari Pencurian',
                'category_id' => 1,
                'excerpt' => 'Keamanan adalah prioritas utama penghuni kos. Terapkan 7 langkah ini untuk mencegah pencurian dan membuat penghuni merasa aman.',
                'content' => '<h2>Statistik Pencurian di Kos</h2><p>Menurut data kepolisian, pencurian di area kos meningkat 20% setiap tahun, terutama saat musim liburan.</p><h2>7 Langkah Keamanan</h2><ol><li><strong>Pasang CCTV</strong> di titik strategis — gerbang, koridor, area parkir</li><li><strong>Kunci ganda</strong> untuk setiap pintu kamar — standar + gembok</li><li><strong>Penerangan cukup</strong> di area gelap — sensor otomatis hemat listrik</li><li><strong>Data penghuni lengkap</strong> — fotokopi KTP, nomor kontak darurat</li><li><strong>Tamu wajib lapor</strong> — aturan ketat identitas tamu menginap</li><li><strong>Satpam atau penjaga</strong> — untuk kos dengan 20+ kamar</li><li><strong>Asuransi properti</strong> — proteksi tambahan dari kebakaran, banjir, pencurian</li></ol><h2>Teknologi Pendukung</h2><p>Gunakan smart lock dengan PIN/kartu akses. Sistem mencatat setiap akses masuk keluar. Kos Manager mendukung integrasi dengan berbagai perangkat keamanan.</p>',
                'is_published' => true,
                'published_at' => now()->subHours(12),
                'meta_title' => 'Keamanan Kos: 7 Langkah Lindungi Penghuni — Blog Kos Manager',
                'meta_description' => 'Cegah pencurian di kos dengan 7 langkah: CCTV, kunci ganda, penerangan, data penghuni, aturan tamu, satpam, asuransi.',
            ],
        ];

        foreach ($artikel as $art) {
            $art['slug'] = Str::slug($art['title']);
            $art['author_id'] = 1; // admin
            BlogPost::create($art);
        }
    }
}
