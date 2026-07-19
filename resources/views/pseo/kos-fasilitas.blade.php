@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Kos dengan Fasilitas {{ $fasilitasName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Dapatkan kos nyaman dengan fasilitas {{ $fasilitasName }}. Harga terjangkau, lokasi strategis.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection

