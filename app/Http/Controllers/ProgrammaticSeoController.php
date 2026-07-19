<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class ProgrammaticSeoController extends Controller
{
    protected array $kota = [
        'jakarta', 'bandung', 'surabaya', 'semarang', 'yogyakarta', 'malang',
        'medan', 'makassar', 'palembang', 'depok', 'tangerang', 'bekasi',
        'bogor', 'denpasar', 'balikpapan', 'manado', 'pekanbaru', 'padang',
        'solo', 'purwokerto', 'cirebon', 'tasikmalaya', 'magelang', 'salatiga',
    ];

    protected array $kampus = [
        'ui', 'itb', 'ugm', 'unair', 'undip', 'ub', 'its', 'unpad',
        'ipb', 'uns', 'unnes', 'unhas', 'unud', 'unri', 'unsri', 'usu',
    ];

    protected array $fasilitas = [
        'ac', 'wifi', 'kamar-mandi-dalam', 'dapur', 'parkir-motor',
        'parkir-mobil', 'tv', 'kasur', 'lemari', 'meja-belajar',
    ];

    protected array $tipe = [
        'putra', 'putri', 'campur', 'harian', 'bulanan', 'tahunan',
    ];

    public function bestCity($city)
    {
        $cityName = ucfirst($city);
        $properties = Property::active()->paginate(10);

        $seo = [
            'title' => "10 Kos Terbaik di {$cityName} — Rekomendasi Hunian Nyaman",
            'description' => "Cari kos terbaik di {$cityName}? Lihat daftar rekomendasi kos murah, strategis, dan nyaman khusus area {$cityName}. Harga mulai Rp 500rb/bulan.",
            'canonical' => route('pseo.best.city', $city),
        ];

        return view('pseo.best-city', compact('city', 'cityName', 'properties', 'seo'));
    }

    public function bestKampus($kampus)
    {
        $kampusName = strtoupper($kampus);
        $properties = Property::active()->paginate(10);

        $seo = [
            'title' => "Kos Dekat {$kampusName} — Hunian Strategis untuk Mahasiswa",
            'description' => "Cari kos dekat kampus {$kampusName}? Daftar kos murah, nyaman, dan dekat dengan {$kampusName}. Cocok untuk mahasiswa. Booking sekarang!",
            'canonical' => route('pseo.best.kampus', $kampus),
        ];

        return view('pseo.best-kampus', compact('kampus', 'kampusName', 'properties', 'seo'));
    }

    public function kosFasilitas($fasilitas)
    {
        $fasilitasName = ucwords(str_replace('-', ' ', $fasilitas));
        $properties = Property::active()->paginate(10);

        $seo = [
            'title' => "Kos {$fasilitasName} — Hunian dengan Fasilitas Lengkap",
            'description' => "Cari kos dengan fasilitas {$fasilitasName}? Temukan kos nyaman dengan {$fasilitasName} di berbagai kota. Harga terjangkau, booking mudah!",
            'canonical' => route('pseo.fasilitas', $fasilitas),
        ];

        return view('pseo.kos-fasilitas', compact('fasilitas', 'fasilitasName', 'properties', 'seo'));
    }

    public function kosTipe($tipe)
    {
        $tipeName = ucfirst($tipe);
        $properties = Property::active()->paginate(10);

        $seo = [
            'title' => "Kos {$tipeName} — Pilihan Hunian Sesuai Kebutuhan Anda",
            'description' => "Cari kos {$tipeName}? Temukan berbagai pilihan kos {$tipeName} dengan harga bersaing. Fasilitas lengkap, lokasi strategis!",
            'canonical' => route('pseo.tipe', $tipe),
        ];

        return view('pseo.kos-tipe', compact('tipe', 'tipeName', 'properties', 'seo'));
    }

    public function kosHarga($range)
    {
        [$min, $max] = match ($range) {
            'dibawah-500rb' => [0, 500000],
            '500rb-1jt' => [500000, 1000000],
            '1jt-2jt' => [1000000, 2000000],
            'diatas-2jt' => [2000000, 999999999],
            default => [0, 999999999],
        };

        $rangeName = match ($range) {
            'dibawah-500rb' => 'di Bawah 500 Ribu',
            '500rb-1jt' => '500 Ribu – 1 Juta',
            '1jt-2jt' => '1 – 2 Juta',
            'diatas-2jt' => 'di Atas 2 Juta',
            default => 'Semua Harga',
        };

        $properties = Property::active()->paginate(10);

        $seo = [
            'title' => "Kos {$rangeName} — Harga Terjangkau, Hunian Nyaman",
            'description' => "Cari kos dengan harga {$rangeName} per bulan? Temukan kos murah berkualitas di berbagai kota. Booking mudah, aman, terpercaya!",
            'canonical' => route('pseo.harga', $range),
        ];

        return view('pseo.kos-harga', compact('range', 'rangeName', 'properties', 'seo'));
    }

    public function alternatif($slug)
    {
        $property = Property::active()->where('slug', $slug)->first();
        $alternatives = Property::active()->where('id', '!=', $property?->id)->inRandomOrder()->take(6)->get();
        $name = $property ? $property->name : ucwords(str_replace('-', ' ', $slug));

        $seo = [
            'title' => "Alternatif {$name} — Pilihan Kos Serupa & Terbaik",
            'description' => "Mencari alternatif {$name}? Lihat daftar kos serupa dengan fasilitas dan harga setara. Bandingkan dan pilih hunian terbaik Anda!",
            'canonical' => route('pseo.alternatif', $slug),
        ];

        return view('pseo.alternatif', compact('slug', 'name', 'property', 'alternatives', 'seo'));
    }

    public function bandingkan($slug)
    {
        $parts = explode('-vs-', $slug);
        $a_slug = $parts[0] ?? '';
        $b_slug = $parts[1] ?? '';

        $propA = Property::active()->where('slug', $a_slug)->first();
        $propB = Property::active()->where('slug', $b_slug)->first();

        $nameA = $propA ? $propA->name : ucwords(str_replace('-', ' ', $a_slug));
        $nameB = $propB ? $propB->name : ucwords(str_replace('-', ' ', $b_slug));

        $seo = [
            'title' => "{$nameA} vs {$nameB} — Perbandingan Kos Mana yang Lebih Baik?",
            'description' => "Bandingkan {$nameA} vs {$nameB}. Lihat perbandingan fasilitas, harga, lokasi, dan review. Pilih kos terbaik untuk kebutuhan Anda!",
            'canonical' => route('pseo.bandingkan', $slug),
        ];

        return view('pseo.bandingkan', compact('a_slug', 'b_slug', 'propA', 'propB', 'nameA', 'nameB', 'seo'));
    }

    public function beliSourceCode($slug = null)
    {
        $seo = [
            'title' => 'Beli Aplikasi Kos Manager — Source Code Siap Pakai',
            'description' => 'Beli source code aplikasi manajemen kos lengkap dengan fitur booking, invoice, dan laporan keuangan. Siap pakai, mudah dikustomisasi!',
            'canonical' => route('pseo.beli'),
        ];

        return view('pseo.beli-source-code', compact('seo'));
    }
}
