@extends('pseo._layout')

@section('content')
<div class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl font-extrabold text-white">Bandingkan Kos</h1>
        <p class="text-primary-100 mt-4 text-lg">Pilih dua kos untuk membandingkan fasilitas, harga, dan lokasi.</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    @if($errors->has('compare'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            {{ $errors->first('compare') }}
        </div>
    @endif
    <form action="{{ route('pseo.bandingkan.form') }}" method="GET" class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label for="compare-a" class="block text-sm font-semibold text-slate-700 mb-2">Kos Pertama</label>
                <select id="compare-a" name="a" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    <option value="">— Pilih kos pertama —</option>
                    @foreach($propertyOptions as $p)
                        <option value="{{ $p->name }}" {{ request('a') === $p->name ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="compare-b" class="block text-sm font-semibold text-slate-700 mb-2">Kos Kedua</label>
                <select id="compare-b" name="b" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    <option value="">— Pilih kos kedua —</option>
                    @foreach($propertyOptions as $p)
                        <option value="{{ $p->name }}" {{ request('b') === $p->name ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/25 hover:-translate-y-0.5 transition-all">Bandingkan Sekarang</button>
    </form>
</div>
@endsection
