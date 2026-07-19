@extends('landing.layouts.app')

@section('title', 'Booking — ' . $property->name)

@section('content')

<section class="gradient-hero text-white pt-24 pb-12 px-4">
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('landing.property', $property) }}" class="text-blue-200 hover:text-white text-sm mb-3 inline-flex items-center gap-1 transition-colors">
            ← Kembali ke Detail Kos
        </a>
        <h1 class="text-3xl font-bold mt-2">Form Booking Online</h1>
        <p class="text-blue-100 mt-1">{{ $property->name }} — {{ $property->address }}</p>
    </div>
</section>

<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Success State --}}
    @if(session('booking_success'))
        <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center mb-8">
            <div class="text-5xl mb-3">✅</div>
            <h2 class="text-xl font-bold text-green-700 mb-2">Permintaan Booking Terkirim!</h2>
            <p class="text-green-600 text-sm mb-4">Kami akan menghubungi Anda dalam 1×24 jam untuk konfirmasi.</p>
            <a href="{{ route('landing.home') }}" class="inline-block bg-blue-600 text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                Kembali ke Beranda
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="font-bold text-lg mb-5 text-gray-800">Isi Data Diri</h2>

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 mb-5 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('booking.store', $property) }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 @error('name') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 @error('phone') border-red-400 @enderror">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}"
                                   placeholder="08xxxxxxxxxx (jika berbeda dengan HP)"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Masuk <span class="text-red-500">*</span></label>
                            <input type="date" name="desired_move_in" value="{{ old('desired_move_in') }}" required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 @error('desired_move_in') border-red-400 @enderror">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Sewa <span class="text-red-500">*</span></label>
                            <select name="billing_cycle" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                @foreach([
                                    'daily'     => 'Harian',
                                    'weekly'    => 'Mingguan',
                                    'monthly'   => 'Bulanan',
                                    'quarterly' => '3 Bulan',
                                    'yearly'    => 'Tahunan',
                                ] as $val => $label)
                                    <option value="{{ $val }}" {{ old('billing_cycle', request('billing_cycle', 'monthly')) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if($roomTypes->isNotEmpty())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kamar yang Diminati</label>
                            <select name="room_type_id"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">— Belum tahu / semua tipe —</option>
                                @foreach($roomTypes as $rt)
                                    <option value="{{ $rt->id }}"
                                        {{ (old('room_type_id', request('room_type')) == $rt->id) ? 'selected' : '' }}>
                                        {{ $rt->name }}
                                        @if($rt->base_price_monthly)
                                            — Rp {{ number_format($rt->base_price_monthly, 0, ',', '.') }}/bln
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pesan / Pertanyaan</label>
                        <textarea name="message" rows="3" placeholder="Tanyakan tentang kamar, fasilitas, atau ketersediaan..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors text-sm">
                        Kirim Permintaan Booking
                    </button>

                    <p class="text-center text-xs text-gray-400">
                        Dengan mengirimkan form ini, Anda setuju untuk dihubungi oleh pengelola kos.
                    </p>
                </form>
            </div>
        </div>

        {{-- Sidebar info kamar --}}
        <div class="space-y-4">

            {{-- Kamar tersedia --}}
            <div class="bg-white rounded-2xl shadow-sm p-5">
                <h3 class="font-bold mb-3 text-gray-800">Kamar Tersedia</h3>
                @if($rooms->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($rooms as $room)
                            <div class="border border-gray-100 rounded-xl p-3">
                                <p class="font-semibold text-sm">Kamar {{ $room->room_number }}</p>
                                @if($room->roomType)
                                    <p class="text-xs text-gray-500">{{ $room->roomType->name }}</p>
                                @endif
                                @if($room->effective_price_daily)
                                    <p class="text-xs text-orange-600 mt-1">Rp {{ number_format($room->effective_price_daily, 0, ',', '.') }}/hari</p>
                                @endif
                                @if($room->effective_price_weekly)
                                    <p class="text-xs text-purple-600">Rp {{ number_format($room->effective_price_weekly, 0, ',', '.') }}/minggu</p>
                                @endif
                                @if($room->effective_price_monthly)
                                    <p class="text-xs text-blue-600 font-semibold">Rp {{ number_format($room->effective_price_monthly, 0, ',', '.') }}/bulan</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-yellow-700 text-xs text-center">
                        Saat ini tidak ada kamar tersedia.<br>Anda tetap bisa mendaftar untuk dihubungi saat ada kamar kosong.
                    </div>
                @endif
            </div>

            {{-- Kontak --}}
            @if(setting('contact_whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}?text={{ urlencode('Halo, saya ingin tanya tentang booking kos ' . $property->name) }}"
                   target="_blank"
                   class="flex items-center justify-center gap-2 bg-green-500 text-white font-semibold py-3 rounded-xl hover:bg-green-600 transition-colors text-sm">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Tanya via WhatsApp
                </a>
            @endif
        </div>
    </div>
</div>

@endsection
