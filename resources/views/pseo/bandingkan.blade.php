@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">{{ $nameA }} vs {{ $nameB }}</h1>
        <p class="text-primary-100 mt-4 text-lg">Perbandingan lengkap dua properti kos. Mana yang lebih cocok untuk Anda?</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
            <h2 class="text-2xl font-bold text-primary-700">{{ $nameA }}</h2>
            @if($propA)
                <p class="text-slate-500 mt-2">{{ $propA->address }}</p>
                <p class="text-slate-500">{{ $propA->description }}</p>
            @endif
            <a href="{{ $propA ? route('landing.property', $propA) : '#' }}" class="inline-block mt-4 bg-primary-600 text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-primary-700 transition-colors">Lihat Detail</a>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
            <h2 class="text-2xl font-bold text-indigo-700">{{ $nameB }}</h2>
            @if($propB)
                <p class="text-slate-500 mt-2">{{ $propB->address }}</p>
                <p class="text-slate-500">{{ $propB->description }}</p>
            @endif
            <a href="{{ $propB ? route('landing.property', $propB) : '#' }}" class="inline-block mt-4 bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-indigo-700 transition-colors">Lihat Detail</a>
        </div>
    </div>
</div>
@endsection

