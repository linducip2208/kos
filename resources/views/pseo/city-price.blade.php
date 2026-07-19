@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Kos {{ $rangeLabel }} di {{ $cityName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Hunian nyaman dengan budget {{ $rangeLabel }} di {{ $cityName }}. Banyak pilihan kos berkualitas.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="aspect-video bg-gradient-to-br from-green-50 to-emerald-50 flex items-center justify-center">
                <div class="text-5xl">💰</div>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada properti dengan harga {{ $rangeLabel }} di {{ $cityName }}.</div>
        @endforelse
    </div>

    <div class="mt-16 prose max-w-3xl mx-auto">
        <h2>Kos Budget {{ $rangeLabel }} di {{ $cityName }}</h2>
        <p>Anda sedang mencari kos dengan anggaran <strong>{{ $rangeLabel }}</strong> di area <strong>{{ $cityName }}</strong>? Kami menyediakan berbagai pilihan kos sesuai budget Anda, tanpa mengorbankan kenyamanan.</p>
        <h3>Tips Mencari Kos Sesuai Budget</h3>
        <ul>
            <li>Tentukan budget maksimal termasuk biaya tambahan (listrik, air, iuran)</li>
            <li>Prioritaskan lokasi dekat tempat aktivitas utama (kampus/kantor)</li>
            <li>Bandingkan 3-5 kos sebelum memutuskan</li>
            <li>Cek kondisi kamar dan fasilitas secara langsung</li>
        </ul>
    </div>
</div>
@endsection
