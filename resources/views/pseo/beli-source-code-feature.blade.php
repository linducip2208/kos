@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-slate-800 via-slate-900 to-indigo-950 py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Aplikasi Kos dengan {{ $featureLabel }}</h1>
        <p class="text-slate-300 mt-6 text-lg">Beli source code aplikasi manajemen kos lengkap dengan fitur {{ $featureLabel }}.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 text-center">
    <h2 class="text-3xl font-extrabold text-slate-800 mb-4">Fitur {{ $featureLabel }} untuk Bisnis Kos Anda</h2>
    <p class="text-lg text-slate-600 max-w-2xl mx-auto mb-8">Aplikasi Kos Manager memiliki fitur {{ $featureLabel }} yang memudahkan pengelolaan kos. Invoice otomatis, portal penghuni, laporan keuangan.</p>
    <a href="{{ route('pseo.beli') }}" class="inline-block bg-primary-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-primary-700 transition-colors">Lihat Harga & Beli Sekarang</a>
</div>
@endsection
