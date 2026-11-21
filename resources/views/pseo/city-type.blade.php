@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Kos {{ $typeLabel }} di {{ $cityName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Hunian khusus {{ $typeLabel }} di {{ $cityName }}. Nyaman, aman, dan sesuai kebutuhan Anda.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada kos {{ $typeLabel }} di {{ $cityName }}.</div>
        @endforelse
    </div>

    <div class="mt-12 prose max-w-3xl mx-auto">
        <h2>Kos {{ $typeLabel }} di {{ $cityName }} — Panduan Lengkap</h2>
        <p>Mencari kos {{ $typeLabel }} di {{ $cityName }}? Daftar di atas berisi properti terverifikasi dengan fasilitas lengkap dan harga transparan. Semua kos {{ $typeLabel }} kami pastikan aman dan nyaman.</p>
        <h3>Pertanyaan Umum</h3>
        <h4>Berapa harga kos {{ $typeLabel }} di {{ $cityName }}?</h4>
        <p>Harga bervariasi mulai Rp 500.000 hingga Rp 3.000.000 per bulan tergantung fasilitas dan lokasi di {{ $cityName }}.</p>
        <h4>Bagaimana cara booking kos {{ $typeLabel }} di {{ $cityName }}?</h4>
        <p>Klik properti di atas, pilih tipe kamar, lalu isi form booking online. Tim kami akan menghubungi dalam 1×24 jam.</p>
    </div>
</div>
@endsection
