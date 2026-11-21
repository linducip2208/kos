@extends('pseo._layout')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="text-3xl font-extrabold text-slate-900">{{ $seo['title'] }}</h1>
    <p class="text-slate-500 mt-4">{{ $seo['description'] }}</p>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-lg transition-all">
            <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
            <p class="text-sm text-slate-500">{{ Str::limit($p->address, 60) }}</p>
            <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada properti pada rentang harga ini. <a href="{{ route('landing.home') }}" class="text-primary-600 font-semibold hover:underline">Lihat semua kos →</a></div>
        @endforelse
    </div>
    <div class="mt-12 prose max-w-3xl">
        <h2>Panduan Harga {{ $rangeLabel }}</h2>
        <p>Kisaran harga {{ $rangeLabel }} per bulan adalah salah satu yang paling dicari. Semua properti di atas telah kami verifikasi ketersediaan dan fasilitasnya. Booking mudah, tanpa biaya tersembunyi.</p>
    </div>
</div>
@endsection

