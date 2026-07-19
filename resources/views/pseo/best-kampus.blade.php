@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Kos Dekat {{ $kampusName }}</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Hunian strategis untuk mahasiswa {{ $kampusName }}. Dekat kampus, harga terjangkau, fasilitas lengkap.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $p)
        <a href="{{ route('landing.property', $p) }}" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
            <div class="aspect-video bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                <svg class="w-16 h-16 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
            </div>
            <div class="p-5">
                <h3 class="font-bold text-slate-800">{{ $p->name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ Str::limit($p->address, 60) }}</p>
                <span class="inline-block mt-3 text-primary-600 font-bold">Lihat Detail →</span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-20 text-slate-500">Belum ada properti tersedia.</div>
        @endforelse
    </div>
</div>
@endsection

