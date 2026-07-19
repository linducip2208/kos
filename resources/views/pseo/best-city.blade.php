@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">10 Kos Terbaik di {{ $cityName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Rekomendasi kos murah, strategis, dan nyaman di {{ $cityName }}. Fasilitas lengkap, harga terjangkau.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="aspect-video bg-gradient-to-br from-primary-100 to-indigo-100 flex items-center justify-center">
                <svg class="w-16 h-16 text-primary-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/></svg>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="text-primary-600 font-bold text-lg">Rp {{ number_format($p->rooms()->min('price_monthly') ?? $p->roomTypes()->min('price_monthly') ?? 500000, 0, ',', '.') }}</span>
                    <span class="text-xs text-slate-400">/bulan</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada properti tersedia.</div>
        @endforelse
    </div>

    <div class="mt-16 prose max-w-3xl mx-auto">
        <h2>Kenapa Harus Kos di {{ $cityName }}?</h2>
        <p>{{ $cityName }} adalah salah satu kota terbaik untuk tinggal. Dengan akses transportasi yang mudah, pusat perbelanjaan, dan universitas ternama, {{ $cityName }} menawarkan kenyamanan hidup yang sulit ditandingi.</p>
        <p>Kami telah melakukan riset untuk menemukan kos-kos terbaik di {{ $cityName }} berdasarkan fasilitas, harga, lokasi, dan review dari penghuni sebelumnya.</p>
        <h3>FAQ</h3>
        <h4>Berapa harga kos di {{ $cityName }}?</h4>
        <p>Harga kos di {{ $cityName }} bervariasi mulai dari Rp 500.000 hingga Rp 3.000.000 per bulan, tergantung fasilitas dan lokasi.</p>
        <h4>Apakah ada kos khusus putra/putri di {{ $cityName }}?</h4>
        <p>Ya, tersedia kos khusus putra dan putri di berbagai area {{ $cityName }}.</p>
    </div>
</div>
@endsection

