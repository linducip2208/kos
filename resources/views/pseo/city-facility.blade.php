@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Kos {{ $facilityName }} di {{ $cityName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Hunian nyaman dengan fasilitas {{ $facilityName }} di {{ $cityName }}. Lokasi strategis, harga terjangkau.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="aspect-video bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center">
                <div class="text-5xl">🏠</div>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada properti dengan fasilitas {{ $facilityName }} di {{ $cityName }}.</div>
        @endforelse
    </div>

    <div class="mt-16 prose max-w-3xl mx-auto">
        <h2>Keunggulan Kos {{ $facilityName }} di {{ $cityName }}</h2>
        <p>Mencari kos dengan fasilitas <strong>{{ $facilityName }}</strong> di <strong>{{ $cityName }}</strong>? Anda datang ke tempat yang tepat. Kami menyediakan daftar kos terbaik dengan {{ $facilityName }} di area {{ $cityName }} dan sekitarnya.</p>
        <h3>Kenapa Memilih Kos dengan {{ $facilityName }}?</h3>
        <p>Fasilitas {{ $facilityName }} memberikan kenyamanan ekstra selama Anda tinggal. Berikut beberapa keuntungannya:</p>
        <ul>
            <li>Kenyamanan maksimal untuk aktivitas sehari-hari</li>
            <li>Mendukung produktivitas kerja dan belajar</li>
            <li>Nilai tambah dibanding kos tanpa fasilitas tersebut</li>
            <li>Investasi untuk kualitas hidup yang lebih baik</li>
        </ul>
    </div>
</div>
@endsection
