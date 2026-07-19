@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Bandingkan Kos</h1>
        <p class="text-primary-100 mt-4 text-lg">Masukkan dua nama kos untuk membandingkan fasilitas, harga, dan lokasi.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <form action="{{ route('pseo.bandingkan.form') }}" method="GET" class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Kos Pertama</label>
                <input type="text" name="a" placeholder="Contoh: Kos Melati" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Kos Kedua</label>
                <input type="text" name="b" placeholder="Contoh: Kos Mawar" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
            </div>
        </div>
        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/25 hover:-translate-y-0.5 transition-all">Bandingkan Sekarang</button>
    </form>
</div>
@endsection
