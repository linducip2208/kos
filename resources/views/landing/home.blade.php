@extends('landing.layouts.app')

@section('title', setting('app_name', 'Kos Manager') . ' — Hunian Kos Terpercaya')
@section('description', 'Temukan hunian kos terbaik dengan fasilitas lengkap, harga terjangkau, dan proses booking mudah.')

@section('content')

{{-- ═══════════════════ HERO ═══════════════════ --}}
<section class="relative min-h-screen gradient-hero flex items-center overflow-hidden">

    <div class="absolute top-1/4 left-10 w-72 h-72 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-32 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

        {{-- Left: text --}}
        <div class="text-white">
            <div class="inline-flex items-center gap-2 glass px-4 py-2 rounded-full text-sm font-medium text-blue-200 mb-6">
                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                Tersedia kamar kosong sekarang
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                Temukan Hunian
                <span class="gradient-text block">Kos Terbaik</span>
                untuk Anda
            </h1>

            <p class="text-lg text-blue-100/80 max-w-lg leading-relaxed mb-10">
                Kamar nyaman, fasilitas lengkap, lokasi strategis — dengan sistem booking online yang mudah dan transparan.
            </p>

            <div class="flex flex-col sm:flex-row gap-4">
                <a href="#properti"
                   class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-8 py-4 rounded-2xl hover:bg-blue-50 transition-all shadow-xl shadow-black/20 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Lihat Kamar Tersedia
                </a>
                @if(setting('contact_whatsapp'))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-2 glass text-white font-semibold px-8 py-4 rounded-2xl hover:bg-white/20 transition-all text-base border border-white/20">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Tanya via WhatsApp
                </a>
                @endif
            </div>
        </div>

        {{-- Right: floating stat cards --}}
        <div class="hidden lg:flex flex-col gap-5 items-end">
            @php
                $totalAvailable = $properties->sum('available_rooms');
                $totalRooms = $properties->sum('total_rooms');
            @endphp

            <div class="glass rounded-2xl px-6 py-5 w-64 float-anim" style="animation-delay:0s">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-white">{{ $totalAvailable }}</div>
                        <div class="text-xs text-blue-200">Kamar Tersedia</div>
                    </div>
                </div>
            </div>

            <div class="glass rounded-2xl px-6 py-5 w-64 float-anim" style="animation-delay:1.5s">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-white">{{ $properties->count() }}</div>
                        <div class="text-xs text-blue-200">Lokasi Properti</div>
                    </div>
                </div>
            </div>

            <div class="glass rounded-2xl px-6 py-5 w-64 float-anim" style="animation-delay:3s">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-white">4.9 ★</div>
                        <div class="text-xs text-blue-200">Rating Penyewa</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 text-white/50 flex flex-col items-center gap-2 text-xs animate-bounce">
        <span>Scroll</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ═══════════════════ PROPERTIES ═══════════════════ --}}
<section id="properti" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Pilihan Hunian</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">Properti Kos Kami</h2>
            <p class="text-slate-500 mt-3 max-w-xl mx-auto">Pilih lokasi yang paling sesuai dengan kebutuhan dan anggaran Anda.</p>
        </div>

        @if($properties->isEmpty())
            <div class="text-center py-20 text-slate-400">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <p class="text-lg font-medium">Belum ada properti tersedia</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($properties as $i => $prop)
                    <a href="{{ route('landing.property', $prop) }}"
                       class="group card-premium bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-md"
                       data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 100 }}">

                        {{-- Photo --}}
                        <div class="relative h-52 bg-gradient-to-br from-slate-200 to-slate-300 overflow-hidden">
                            @if(!empty($prop->photos[0]))
                                <img src="{{ asset('storage/' . $prop->photos[0]) }}"
                                     alt="{{ $prop->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="absolute inset-0 gradient-hero flex items-center justify-center">
                                    <svg class="w-20 h-20 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                </div>
                            @endif

                            <div class="absolute top-4 left-4">
                                @if($prop->available_rooms > 0)
                                    <span class="bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                        {{ $prop->available_rooms }} Kamar Tersedia
                                    </span>
                                @else
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                        Penuh
                                    </span>
                                @endif
                            </div>

                            @if(!empty($prop->photos) && count($prop->photos) > 1)
                                <div class="absolute bottom-4 right-4 glass text-white text-xs px-2.5 py-1 rounded-lg">
                                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ count($prop->photos) }} foto
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-6">
                            <h3 class="font-extrabold text-xl text-slate-900 group-hover:text-primary-700 transition-colors mb-1">
                                {{ $prop->name }}
                            </h3>
                            <p class="text-slate-500 text-sm flex items-center gap-1.5 mb-3">
                                <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $prop->city }}{{ $prop->province ? ', ' . $prop->province : '' }}
                            </p>

                            @php
                                $lowestPrice = $prop->roomTypes->whereNotNull('base_price_monthly')->min('base_price_monthly');
                                $lowestDaily = $prop->roomTypes->whereNotNull('base_price_daily')->min('base_price_daily');
                            @endphp
                            @if($lowestPrice || $lowestDaily)
                                <div class="price-badge rounded-xl px-3 py-2 mb-4 inline-flex items-baseline gap-1.5">
                                    <span class="text-slate-500 text-xs">Mulai</span>
                                    @if($lowestPrice)
                                        <span class="text-primary-700 font-extrabold text-lg">
                                            Rp {{ number_format($lowestPrice, 0, ',', '.') }}
                                        </span>
                                        <span class="text-slate-400 text-xs">/bulan</span>
                                    @elseif($lowestDaily)
                                        <span class="text-primary-700 font-extrabold text-lg">
                                            Rp {{ number_format($lowestDaily, 0, ',', '.') }}
                                        </span>
                                        <span class="text-slate-400 text-xs">/hari</span>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($prop->facilities))
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach(array_slice($prop->facilities, 0, 4) as $fac)
                                        <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">{{ $fac }}</span>
                                    @endforeach
                                    @if(count($prop->facilities) > 4)
                                        <span class="text-xs text-slate-400 px-1 py-1">+{{ count($prop->facilities) - 4 }} lagi</span>
                                    @endif
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <div class="text-xs text-slate-400">
                                    {{ $prop->total_rooms }} kamar total
                                </div>
                                <div class="flex items-center gap-1.5 text-primary-600 text-sm font-semibold group-hover:gap-2.5 transition-all">
                                    Lihat Detail
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ═══════════════════ WHY US ═══════════════════ --}}
<section id="fasilitas" class="py-20 bg-gradient-to-b from-slate-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Keunggulan Kami</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">Mengapa Pilih Kami?</h2>
            <p class="text-slate-500 mt-3 max-w-xl mx-auto">Kami hadir untuk memberikan pengalaman kos yang nyaman, aman, dan mudah dikelola.</p>
        </div>

        @php
        $features = [
            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Aman & Terpercaya', 'desc' => 'Sistem keamanan 24 jam, CCTV, dan pengelola profesional yang responsif setiap saat.', 'color' => 'bg-green-100 text-green-600'],
            ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Harga Transparan', 'desc' => 'Pilihan sewa harian, mingguan, bulanan, hingga tahunan. Tidak ada biaya tersembunyi.', 'color' => 'bg-blue-100 text-blue-600'],
            ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Booking Cepat & Mudah', 'desc' => 'Proses reservasi online dalam hitungan menit. Konfirmasi langsung tanpa antri.', 'color' => 'bg-yellow-100 text-yellow-600'],
            ['icon' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z', 'title' => 'Fasilitas Lengkap', 'desc' => 'WiFi kencang, AC, kamar mandi dalam, dapur bersama, dan berbagai fasilitas modern.', 'color' => 'bg-purple-100 text-purple-600'],
            ['icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z', 'title' => 'Dukungan 24/7', 'desc' => 'Tim kami siap membantu kapan saja. Hubungi lewat WhatsApp atau telepon.', 'color' => 'bg-red-100 text-red-600'],
            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Kontrak Fleksibel', 'desc' => 'Pilih durasi sewa sesuai kebutuhan. Mulai dari harian hingga tahunan tersedia.', 'color' => 'bg-indigo-100 text-indigo-600'],
        ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($features as $i => $feat)
                <div class="card-premium bg-white border border-slate-100 rounded-3xl p-7 shadow-sm"
                     data-aos="fade-up" data-aos-delay="{{ ($i % 3) * 100 }}">
                    <div class="w-14 h-14 {{ $feat['color'] }} rounded-2xl flex items-center justify-center mb-5">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $feat['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-900 mb-2">{{ $feat['title'] }}</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
<section class="py-20 gradient-hero relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-blue-300 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14" data-aos="fade-up">
            <span class="text-blue-300 font-semibold text-sm uppercase tracking-wider">Proses Mudah</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">Cara Booking Kamar</h2>
            <p class="text-blue-100/70 mt-3">3 langkah mudah untuk mendapatkan kamar impian Anda</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @php
            $steps = [
                ['num' => '01', 'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'title' => 'Pilih Kamar', 'desc' => 'Jelajahi pilihan kamar dan tipe yang tersedia. Lihat foto, fasilitas, dan harga detail.'],
                ['num' => '02', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Isi Formulir', 'desc' => 'Lengkapi data diri dan pilih tanggal mulai huni serta durasi sewa yang diinginkan.'],
                ['num' => '03', 'icon' => 'M5 13l4 4L19 7', 'title' => 'Konfirmasi & Huni', 'desc' => 'Tim kami akan menghubungi untuk konfirmasi. Lakukan pembayaran dan langsung huni!'],
            ];
            @endphp

            @foreach($steps as $i => $step)
                <div class="glass rounded-3xl p-8 text-center text-white" data-aos="fade-up" data-aos-delay="{{ $i * 150 }}">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-5 relative">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                        </svg>
                        <span class="absolute -top-2 -right-2 w-6 h-6 bg-white text-primary-700 rounded-full text-xs font-extrabold flex items-center justify-center">
                            {{ $step['num'] }}
                        </span>
                    </div>
                    <h3 class="font-extrabold text-xl mb-3">{{ $step['title'] }}</h3>
                    <p class="text-blue-100/75 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════ TESTIMONIALS ═══════════════════ --}}
@if($testimonials->isNotEmpty())
<section id="testimoni" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-14" data-aos="fade-up">
            <span class="text-primary-600 font-semibold text-sm uppercase tracking-wider">Kata Mereka</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-2">Testimoni Penyewa</h2>
            <p class="text-slate-500 mt-3">Apa kata penyewa kami tentang pengalaman mereka.</p>
        </div>

        <div class="swiper testimonialSwiper" data-aos="fade-up" data-aos-delay="100">
            <div class="swiper-wrapper pb-12">
                @foreach($testimonials as $t)
                    <div class="swiper-slide h-auto">
                        <div class="bg-white rounded-3xl p-7 shadow-sm border border-slate-100 flex flex-col h-full">
                            <div class="flex gap-1 mb-4">
                                @for($s = 1; $s <= 5; $s++)
                                    <svg class="w-5 h-5 {{ $s <= $t->rating ? 'text-yellow-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>

                            <p class="text-slate-700 text-sm leading-relaxed flex-1 mb-5 italic">"{{ $t->content }}"</p>

                            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                                @if($t->avatar)
                                    <img src="{{ asset('storage/' . $t->avatar) }}" alt="{{ $t->name }}" class="w-11 h-11 rounded-full object-cover">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($t->name) }}&background=2563eb&color=fff&size=44" alt="{{ $t->name }}" class="w-11 h-11 rounded-full">
                                @endif
                                <div>
                                    <div class="font-bold text-sm text-slate-900">{{ $t->name }}</div>
                                    @if($t->occupation)
                                        <div class="text-xs text-slate-400">{{ $t->occupation }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
@endif

{{-- ═══════════════════ CTA ═══════════════════ --}}
<section id="kontak" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">

        <div class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-10 md:p-14 text-white text-center relative overflow-hidden" data-aos="zoom-in">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
            <div class="relative">
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Siap Temukan Kamar Impian?</h2>
                <p class="text-blue-100 text-lg mb-10 max-w-xl mx-auto">Hubungi kami sekarang atau langsung cek kamar yang tersedia. Kami siap membantu!</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#properti" class="inline-flex items-center justify-center gap-2 bg-white text-primary-700 font-bold px-8 py-4 rounded-2xl hover:bg-blue-50 transition-all shadow-lg text-base">
                        Lihat Kamar Tersedia
                    </a>
                    @if(setting('contact_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}"
                       target="_blank"
                       class="inline-flex items-center justify-center gap-2 bg-green-500 text-white font-bold px-8 py-4 rounded-2xl hover:bg-green-600 transition-all shadow-lg text-base">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp Kami
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="mt-14 grid grid-cols-1 md:grid-cols-2 gap-10 items-start">
            <div data-aos="fade-right">
                <h3 class="text-2xl font-extrabold text-slate-900 mb-3">Kirim Pesan</h3>
                <p class="text-slate-500 mb-6 text-sm leading-relaxed">Ada pertanyaan? Tim kami akan membalas dalam 1x24 jam kerja.</p>
                <div class="space-y-4 text-sm">
                    @if(setting('contact_phone'))
                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        {{ setting('contact_phone') }}
                    </div>
                    @endif
                    @if(setting('contact_email'))
                    <div class="flex items-center gap-3 text-slate-600">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        {{ setting('contact_email') }}
                    </div>
                    @endif
                    @if(setting('contact_address'))
                    <div class="flex items-start gap-3 text-slate-600">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span>{{ setting('contact_address') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div data-aos="fade-left">
                @if(session('contact_success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl p-5 text-sm font-medium mb-4">
                        ✓ Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.
                    </div>
                @endif
                <form method="POST" action="{{ route('landing.contact') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                   placeholder="Nama lengkap">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">No. WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required
                                   class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                   placeholder="08xx-xxxx-xxxx">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                               placeholder="email@anda.com (opsional)">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pesan <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="4" required
                                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                                  placeholder="Tanyakan tentang kamar, harga, atau fasilitas...">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full bg-primary-600 text-white font-bold py-3.5 rounded-xl hover:bg-primary-700 transition-all shadow-md shadow-blue-500/20 text-sm">
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper !== 'undefined') {
        new Swiper('.testimonialSwiper', {
            slidesPerView: 1,
            spaceBetween: 24,
            pagination: { el: '.swiper-pagination', clickable: true },
            breakpoints: {
                640:  { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
            },
            autoplay: { delay: 4500, disableOnInteraction: false },
            loop: {{ $testimonials->count() > 3 ? 'true' : 'false' }},
        });
    }
});
</script>

@endsection
