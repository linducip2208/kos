@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Alternatif {{ $name }}</h1>
        <p class="text-primary-100 mt-4 text-lg">Pilihan kos serupa dengan fasilitas dan harga setara dengan {{ $name }}.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    @if($property)
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 mb-8">
        <h2 class="text-2xl font-bold text-slate-800">{{ $property->name }}</h2>
        <p class="text-slate-500 mt-2">{{ $property->address }}</p>
    </div>
    @endif

    <h3 class="text-xl font-bold text-slate-800 mb-6">Alternatif Lainnya</h3>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($alternatives as $alt)
        <a href="{{ route('landing.property', $alt) }}" class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <h4 class="font-bold text-slate-800">{{ $alt->name }}</h4>
            <p class="text-sm text-slate-500 mt-1">{{ Str::limit($alt->address, 60) }}</p>
            <span class="inline-block mt-3 text-primary-600 font-bold text-sm">Lihat Detail →</span>
        </a>
        @endforeach
    </div>
</div>
@endsection

