<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\Seo\PseoDataService;
use Illuminate\Http\Request;

class ProgrammaticSeoController extends Controller
{
    // ───────── Single City ─────────
    public function bestCity($city)
    {
        $cityName = $this->cityLabel($city);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "10 Kos Terbaik di {$cityName} — Rekomendasi Hunian Nyaman",
            "Cari kos terbaik di {$cityName}? Lihat rekomendasi kos murah, strategis, dan nyaman di {$cityName}. Booking mudah!",
            route('pseo.best.city', $city)
        );

        return view('pseo.best-city', compact('city', 'cityName', 'properties', 'seo'));
    }

    // ───────── City + Facility Combo ─────────
    public function cityFacility($city, $facility)
    {
        $cityName = $this->cityLabel($city);
        $facilityName = $this->facilityLabel($facility);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$facilityName} di {$cityName} — Hunian Nyaman Fasilitas Lengkap",
            "Cari kos dengan fasilitas {$facilityName} di {$cityName}? Temukan kos murah, bersih, dan nyaman dengan {$facilityName}. Booking sekarang!",
            route('pseo.city-facility', ['city' => $city, 'facility' => $facility])
        );

        return view('pseo.city-facility', compact('city', 'cityName', 'facility', 'facilityName', 'properties', 'seo'));
    }

    // ───────── City + Price Range Combo ─────────
    public function cityPrice($city, $range)
    {
        $cityName = $this->cityLabel($city);
        $ranges = PseoDataService::priceRanges();
        if (!isset($ranges[$range])) abort(404);
        $rangeLabel = $this->priceLabel($range);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$rangeLabel} di {$cityName} — Harga Terjangkau, Nyaman",
            "Cari kos dengan harga {$rangeLabel} di {$cityName}. Lokasi strategis, fasilitas lengkap. Booking mudah!",
            route('pseo.city-price', ['city' => $city, 'range' => $range])
        );

        return view('pseo.city-price', compact('city', 'cityName', 'range', 'rangeLabel', 'properties', 'seo'));
    }

    // ───────── City + Type Combo ─────────
    public function cityType($city, $type)
    {
        $cityName = $this->cityLabel($city);
        $typeLabel = $this->typeLabel($type);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$typeLabel} di {$cityName} — Hunian Tepat Sesuai Kebutuhan",
            "Cari kos {$typeLabel} di {$cityName}? Pilihan kos {$typeLabel} dengan fasilitas lengkap dan harga bersaing.",
            route('pseo.city-type', ['city' => $city, 'type' => $type])
        );

        return view('pseo.city-type', compact('city', 'cityName', 'type', 'typeLabel', 'properties', 'seo'));
    }

    // ───────── Campus Near City ─────────
    public function campusCity($kampus, $city = null)
    {
        $kampusName = strtoupper($kampus);
        $cityName = $city ? $this->cityLabel($city) : '';
        $subTitle = $city ? " di {$cityName}" : '';
        $properties = Property::active()->latest()->take(12)->get();

        $route = $city
            ? route('pseo.campus-city', ['kampus' => $kampus, 'city' => $city])
            : route('pseo.best.kampus', $kampus);

        $seo = $this->seo(
            "Kos Dekat {$kampusName}{$subTitle} — Hunian Strategis Mahasiswa",
            "Cari kos dekat {$kampusName}{$subTitle}? Kos murah dan nyaman, dekat kampus. Fasilitas lengkap untuk mahasiswa.",
            $route
        );

        return view('pseo.best-kampus', compact('kampus', 'kampusName', 'city', 'cityName', 'properties', 'seo'));
    }

    // ───────── Facility (standalone) ─────────
    public function kosFasilitas($fasilitas)
    {
        $facilityName = $this->facilityLabel($fasilitas);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$facilityName} — Hunian dengan Fasilitas Lengkap",
            "Cari kos dengan fasilitas {$facilityName}? Temukan kos nyaman dengan {$facilityName} di berbagai kota.",
            route('pseo.fasilitas', $fasilitas)
        );

        return view('pseo.kos-fasilitas', compact('fasilitas', 'facilityName', 'properties', 'seo'));
    }

    // ───────── Price Range (standalone) ─────────
    public function kosHarga($range)
    {
        $ranges = PseoDataService::priceRanges();
        if (!isset($ranges[$range])) abort(404);
        $rangeLabel = $this->priceLabel($range);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$rangeLabel} — Harga Terjangkau, Hunian Nyaman",
            "Cari kos dengan harga {$rangeLabel} per bulan. Booking mudah, aman, terpercaya!",
            route('pseo.harga', $range)
        );

        return view('pseo.kos-harga', compact('range', 'rangeLabel', 'properties', 'seo'));
    }

    // ───────── Type (standalone) ─────────
    public function kosTipe($tipe)
    {
        $typeLabel = $this->typeLabel($tipe);
        $properties = Property::active()->latest()->take(12)->get();

        $seo = $this->seo(
            "Kos {$typeLabel} — Pilihan Hunian Sesuai Kebutuhan",
            "Cari kos {$typeLabel}? Temukan berbagai pilihan kos {$typeLabel} dengan harga bersaing di seluruh Indonesia.",
            route('pseo.tipe', $tipe)
        );

        return view('pseo.kos-tipe', compact('tipe', 'typeLabel', 'properties', 'seo'));
    }

    // ───────── Alternatif ─────────
    public function alternatif($slug)
    {
        $property = Property::active()->where('slug', $slug)->first();
        $alternatives = Property::active()->where('id', '!=', $property?->id)->inRandomOrder()->take(6)->get();
        $name = $property ? $property->name : ucwords(str_replace('-', ' ', $slug));

        $seo = $this->seo(
            "Alternatif {$name} — Pilihan Kos Serupa & Terbaik",
            "Mencari alternatif {$name}? Lihat daftar kos serupa dengan fasilitas dan harga setara.",
            route('pseo.alternatif', $slug)
        );

        return view('pseo.alternatif', compact('slug', 'name', 'property', 'alternatives', 'seo'));
    }

    // ───────── Bandingkan ─────────
    public function bandingkan(Request $request, $slug = null)
    {
        if ($slug && str_contains($slug, '-vs-')) {
            $parts = explode('-vs-', $slug);
            $a_slug = $parts[0]; $b_slug = $parts[1];
            $propA = Property::active()->where('slug', $a_slug)->first();
            $propB = Property::active()->where('slug', $b_slug)->first();
            $nameA = $propA ? $propA->name : ucwords(str_replace('-', ' ', $a_slug));
            $nameB = $propB ? $propB->name : ucwords(str_replace('-', ' ', $b_slug));

            $seo = $this->seo(
                "{$nameA} vs {$nameB} — Perbandingan Kos Mana yang Lebih Baik?",
                "Bandingkan {$nameA} vs {$nameB}. Lihat perbandingan fasilitas, harga, lokasi.",
                route('pseo.bandingkan', $slug)
            );

            return view('pseo.bandingkan', compact('a_slug', 'b_slug', 'propA', 'propB', 'nameA', 'nameB', 'seo'));
        }

        // Comparison form page — user types two kos names
        $seo = $this->seo(
            'Bandingkan Kos — Mana yang Lebih Baik untuk Anda?',
            'Bandingkan dua kos favorit Anda. Fasilitas, harga, lokasi. Pilih yang terbaik!',
            route('pseo.bandingkan.form')
        );

        return view('pseo.bandingkan-form', compact('seo'));
    }

    // ───────── Source Code Selling ─────────
    public function beliSourceCode($slug = null)
    {
        $seo = $this->seo(
            'Beli Aplikasi Kos Manager — Source Code Siap Pakai',
            'Beli source code aplikasi manajemen kos lengkap. Fitur booking, invoice, laporan. Siap pakai!',
            route('pseo.beli')
        );

        return view('pseo.beli-source-code', compact('seo'));
    }

    public function beliSourceCodeByCity($city)
    {
        $cityName = $this->cityLabel($city);

        $seo = $this->seo(
            "Beli Aplikasi Kos di {$cityName} — Source Code Manajemen Kos",
            "Punya bisnis kos di {$cityName}? Beli aplikasi manajemen kos: booking online, invoice otomatis, laporan keuangan.",
            route('pseo.beli.city', $city)
        );

        return view('pseo.beli-source-code-city', compact('city', 'cityName', 'seo'));
    }

    public function beliSourceCodeByFeature($feature)
    {
        $featureLabel = $this->facilityLabel($feature);

        $seo = $this->seo(
            "Aplikasi Kos dengan {$featureLabel} — Source Code Siap Pakai",
            "Beli aplikasi manajemen kos dengan fitur {$featureLabel}. Invoice otomatis, booking, laporan keuangan.",
            route('pseo.beli.feature', $feature)
        );

        return view('pseo.beli-source-code-feature', compact('feature', 'featureLabel', 'seo'));
    }

    // ═══════════════ HELPERS ═══════════════

    protected function seo(string $title, string $desc, string $canonical): array
    {
        return [
            'title' => $title,
            'description' => $desc,
            'canonical' => $canonical,
        ];
    }

    protected function cityLabel(string $slug): string
    {
        return ucwords(str_replace('-', ' ', $slug));
    }

    protected function facilityLabel(string $slug): string
    {
        return ucwords(str_replace('-', ' ', $slug));
    }

    protected function priceLabel(string $key): string
    {
        return match ($key) {
            'dibawah-500rb' => 'di Bawah 500 Ribu',
            '500rb-1jt' => '500 Ribu – 1 Juta',
            '1jt-15jt' => '1 – 1,5 Juta',
            '15jt-2jt' => '1,5 – 2 Juta',
            '2jt-3jt' => '2 – 3 Juta',
            'diatas-3jt' => 'di Atas 3 Juta',
            default => ucwords(str_replace('-', ' ', $key)),
        };
    }

    protected function typeLabel(string $key): string
    {
        return match ($key) {
            'putra' => 'Putra',
            'putri' => 'Putri',
            'campur' => 'Campur',
            'harian' => 'Harian',
            'bulanan' => 'Bulanan',
            'tahunan' => 'Tahunan',
            'pegawai' => 'Karyawan / Pegawai',
            'mahasiswa' => 'Mahasiswa',
            'keluarga' => 'Keluarga',
            'eksklusif' => 'Eksklusif',
            default => ucfirst($key),
        };
    }
}
