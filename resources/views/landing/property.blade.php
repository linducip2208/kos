@extends('landing.layouts.app')

@section('title', $property->name . ' — ' . setting('app_name', 'Kos Manager'))
@section('description', strip_tags($property->description ?? 'Kos di ' . $property->city . ' dengan fasilitas lengkap dan harga terjangkau.'))

@section('content')

{{-- ═══════════════════ HERO / GALLERY ═══════════════════ --}}
<section class="relative pt-16">
    @if(!empty($property->photos) && count($property->photos) > 0)
        <div class="swiper gallerySwiper h-[50vh] md:h-[60vh] bg-slate-900">
            <div class="swiper-wrapper">
                @foreach($property->photos as $photo)
                    <div class="swiper-slide">
                        <img src="{{ asset('storage/' . $photo) }}"
                             alt="{{ $property->name }}"
                             class="w-full h-full object-cover opacity-90">
                    </div>
                @endforeach
            </div>
            @if(count($property->photos) > 1)
                <div class="swiper-button-next !text-white !bg-black/40 !w-10 !h-10 rounded-full after:!text-sm"></div>
                <div class="swiper-button-prev !text-white !bg-black/40 !w-10 !h-10 rounded-full after:!text-sm"></div>
                <div class="swiper-pagination"></div>
            @endif

            {{-- Overlay gradient --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent pointer-events-none z-10"></div>

            {{-- Property name overlay --}}
            <div class="absolute bottom-0 left-0 right-0 z-20 p-6 md:p-10 max-w-7xl mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            @if($availableTotal > 0)
                                <span class="bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                                    {{ $availableTotal }} Kamar Tersedia
                                </span>
                            @else
                                <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                                    Penuh
                                </span>
                            @endif
                            @if(!empty($property->photos))
                                <span class="glass text-white text-xs px-2.5 py-1 rounded-lg">
                                    {{ count($property->photos) }} foto
                                </span>
                            @endif
                        </div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-lg">{{ $property->name }}</h1>
                        <p class="text-white/80 text-sm mt-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $property->address }}{{ $property->city ? ', ' . $property->city : '' }}{{ $property->province ? ', ' . $property->province : '' }}
                        </p>
                    </div>

                    {{-- Share button --}}
                    <button onclick="navigator.share ? navigator.share({title: '{{ addslashes($property->name) }}', url: window.location.href}) : navigator.clipboard.writeText(window.location.href).then(() => alert('Link disalin!'))"
                            class="glass text-white text-sm px-4 py-2 rounded-xl flex items-center gap-2 hover:bg-white/20 transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Bagikan
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="gradient-hero h-[40vh] relative pt-16 flex items-end">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-8 w-full">
                <div class="flex items-center gap-2 mb-2">
                    @if($availableTotal > 0)
                        <span class="bg-green-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">{{ $availableTotal }} Kamar Tersedia</span>
                    @else
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full">Penuh</span>
                    @endif
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white">{{ $property->name }}</h1>
                <p class="text-white/80 text-sm mt-1">{{ $property->address }}{{ $property->city ? ', ' . $property->city : '' }}</p>
            </div>
        </div>
    @endif
</section>

{{-- ═══════════════════ MAIN CONTENT ═══════════════════ --}}
<section class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: Main content --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Description --}}
                @if($property->description)
                <div class="bg-white rounded-3xl p-7 shadow-sm" data-aos="fade-up">
                    <h2 class="text-xl font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tentang Properti Ini
                    </h2>
                    <div class="prose prose-sm max-w-none text-slate-600 leading-relaxed">
                        {!! $property->description !!}
                    </div>
                </div>
                @endif

                {{-- Facilities --}}
                @if(!empty($property->facilities))
                <div class="bg-white rounded-3xl p-7 shadow-sm" data-aos="fade-up">
                    <h2 class="text-xl font-extrabold text-slate-900 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Fasilitas Properti
                    </h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($property->facilities as $fac)
                            <div class="flex items-center gap-2.5 bg-slate-50 rounded-xl px-3 py-2.5">
                                <div class="w-2 h-2 bg-primary-500 rounded-full flex-shrink-0"></div>
                                <span class="text-sm text-slate-700">{{ $fac }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Room Types ═══════════════════ --}}
                <div id="kamar" data-aos="fade-up">
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Tipe Kamar
                    </h2>

                    @if($roomTypes->isEmpty())
                        <div class="bg-white rounded-3xl p-10 text-center text-slate-400 shadow-sm">
                            Belum ada tipe kamar yang tersedia.
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($roomTypes as $i => $type)
                                <div class="card-premium bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100"
                                     data-aos="fade-up" data-aos-delay="{{ $i * 80 }}">
                                    <div class="grid grid-cols-1 md:grid-cols-5">

                                        {{-- Room photo --}}
                                        <div class="md:col-span-2 relative h-48 md:h-auto bg-slate-200 overflow-hidden">
                                            @if(!empty($type->photos[0]))
                                                <img src="{{ asset('storage/' . $type->photos[0]) }}"
                                                     alt="{{ $type->name }}"
                                                     class="w-full h-full object-cover">
                                            @else
                                                <div class="absolute inset-0 bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center">
                                                    <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif

                                            {{-- Availability --}}
                                            <div class="absolute top-3 left-3">
                                                @if($type->available_count > 0)
                                                    <span class="bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                                        {{ $type->available_count }} Tersedia
                                                    </span>
                                                @else
                                                    <span class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow">
                                                        Penuh
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Room info --}}
                                        <div class="md:col-span-3 p-6">
                                            <div class="flex items-start justify-between gap-3 mb-3">
                                                <h3 class="text-lg font-extrabold text-slate-900">{{ $type->name }}</h3>
                                                <div class="flex items-center gap-3 text-xs text-slate-400 shrink-0">
                                                    @if($type->size_sqm)
                                                        <span class="bg-slate-100 px-2 py-1 rounded-lg">{{ $type->size_sqm }} m²</span>
                                                    @endif
                                                    @if($type->max_occupants)
                                                        <span class="bg-slate-100 px-2 py-1 rounded-lg">Max {{ $type->max_occupants }} org</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($type->description)
                                                <div class="text-slate-500 text-sm leading-relaxed mb-4 line-clamp-3">
                                                    {!! strip_tags($type->description) !!}
                                                </div>
                                            @endif

                                            {{-- Pricing grid --}}
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-4">
                                                @if($type->base_price_daily)
                                                    <div class="price-badge rounded-xl p-2.5 text-center">
                                                        <div class="text-xs text-slate-500 mb-0.5">Per Hari</div>
                                                        <div class="font-extrabold text-primary-700 text-sm">Rp {{ number_format($type->base_price_daily, 0, ',', '.') }}</div>
                                                    </div>
                                                @endif
                                                @if($type->base_price_weekly)
                                                    <div class="price-badge rounded-xl p-2.5 text-center">
                                                        <div class="text-xs text-slate-500 mb-0.5">Per Minggu</div>
                                                        <div class="font-extrabold text-primary-700 text-sm">Rp {{ number_format($type->base_price_weekly, 0, ',', '.') }}</div>
                                                    </div>
                                                @endif
                                                @if($type->base_price_monthly)
                                                    <div class="bg-blue-600 rounded-xl p-2.5 text-center">
                                                        <div class="text-xs text-blue-200 mb-0.5">Per Bulan</div>
                                                        <div class="font-extrabold text-white text-sm">Rp {{ number_format($type->base_price_monthly, 0, ',', '.') }}</div>
                                                    </div>
                                                @endif
                                                @if($type->base_price_quarterly)
                                                    <div class="price-badge rounded-xl p-2.5 text-center">
                                                        <div class="text-xs text-slate-500 mb-0.5">Per 3 Bulan</div>
                                                        <div class="font-extrabold text-primary-700 text-sm">Rp {{ number_format($type->base_price_quarterly, 0, ',', '.') }}</div>
                                                    </div>
                                                @endif
                                                @if($type->base_price_yearly)
                                                    <div class="price-badge rounded-xl p-2.5 text-center">
                                                        <div class="text-xs text-slate-500 mb-0.5">Per Tahun</div>
                                                        <div class="font-extrabold text-primary-700 text-sm">Rp {{ number_format($type->base_price_yearly, 0, ',', '.') }}</div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Room type facilities --}}
                                            @if(!empty($type->facilities))
                                                <div class="flex flex-wrap gap-1.5 mb-4">
                                                    @foreach(array_slice($type->facilities, 0, 5) as $fac)
                                                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-lg">{{ $fac }}</span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($type->available_count > 0)
                                                <a href="{{ route('booking.show', $property) . '?room_type=' . $type->id }}"
                                                   class="inline-flex items-center gap-2 bg-primary-600 text-white text-sm font-bold px-6 py-3 rounded-xl hover:bg-primary-700 transition-all shadow-md shadow-blue-500/20">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Booking Sekarang
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-400 text-sm font-semibold px-6 py-3 rounded-xl cursor-not-allowed">
                                                    Tidak Tersedia
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Testimonials ═══════════════════ --}}
                @if($testimonials->isNotEmpty())
                <div id="testimoni" data-aos="fade-up">
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Testimoni Penyewa
                    </h2>

                    <div class="swiper propTestiSwiper">
                        <div class="swiper-wrapper pb-10">
                            @foreach($testimonials as $t)
                                <div class="swiper-slide h-auto">
                                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex flex-col h-full">
                                        <div class="flex gap-1 mb-3">
                                            @for($s = 1; $s <= 5; $s++)
                                                <svg class="w-4 h-4 {{ $s <= $t->rating ? 'text-yellow-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <p class="text-slate-600 text-sm leading-relaxed flex-1 italic mb-4">"{{ $t->content }}"</p>
                                        <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                                            @if($t->avatar)
                                                <img src="{{ asset('storage/' . $t->avatar) }}" alt="{{ $t->name }}" class="w-9 h-9 rounded-full object-cover">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($t->name) }}&background=2563eb&color=fff&size=36" alt="{{ $t->name }}" class="w-9 h-9 rounded-full">
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
                        <div class="swiper-pagination !bottom-0"></div>
                    </div>
                </div>
                @endif

                {{-- FAQ ═══════════════════ --}}
                @if($faqs->isNotEmpty())
                <div id="faq" data-aos="fade-up">
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Pertanyaan Umum
                    </h2>
                    <div class="space-y-3" x-data="{ open: null }">
                        @foreach($faqs as $fi => $faq)
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                <button @click="open === {{ $fi }} ? open = null : open = {{ $fi }}"
                                        class="w-full flex items-center justify-between px-6 py-4 text-left">
                                    <span class="font-semibold text-slate-800 text-sm pr-4">{{ $faq->question }}</span>
                                    <svg class="w-5 h-5 text-slate-400 flex-shrink-0 transition-transform duration-300"
                                         :class="open === {{ $fi }} ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open === {{ $fi }}" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-2"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="px-6 pb-5 text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-4">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Google Maps ═══════════════════ --}}
                @if($property->latitude && $property->longitude)
                <div data-aos="fade-up">
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        Lokasi
                    </h2>
                    <div class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-100">
                        <iframe
                            src="https://maps.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}&z=15&output=embed"
                            width="100%"
                            height="320"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="p-4 text-sm text-slate-600 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $property->address }}{{ $property->city ? ', ' . $property->city : '' }}
                            <a href="https://maps.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}"
                               target="_blank"
                               class="ml-auto text-primary-600 font-semibold hover:underline text-xs">
                                Buka di Google Maps →
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Contact Form ═══════════════════ --}}
                <div id="kontak" data-aos="fade-up">
                    <h2 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Hubungi Kami
                    </h2>

                    <div class="bg-white rounded-3xl p-7 shadow-sm">
                        @if(session('contact_success'))
                            <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl p-4 text-sm font-medium mb-5">
                                ✓ Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('landing.contact') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="property_id" value="{{ $property->id }}">
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
                                <textarea name="message" rows="3" required
                                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                                          placeholder="Saya ingin menanyakan tentang kamar di {{ $property->name }}...">{{ old('message') }}</textarea>
                            </div>
                            <button type="submit"
                                    class="w-full bg-primary-600 text-white font-bold py-3.5 rounded-xl hover:bg-primary-700 transition-all shadow-md shadow-blue-500/20 text-sm">
                                Kirim Pesan
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            {{-- RIGHT: Sticky sidebar ═══════════════════ --}}
            <div class="lg:col-span-1">
                <div class="sticky top-24 space-y-5">

                    {{-- Quick CTA Card --}}
                    <div class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-blue-500/20">
                        <div class="text-sm text-blue-200 mb-1 font-medium">Harga mulai dari</div>
                        @php
                            $minMonthly = $roomTypes->whereNotNull('base_price_monthly')->min('base_price_monthly');
                            $minDaily   = $roomTypes->whereNotNull('base_price_daily')->min('base_price_daily');
                        @endphp
                        @if($minMonthly)
                            <div class="text-3xl font-extrabold mb-0.5">Rp {{ number_format($minMonthly, 0, ',', '.') }}</div>
                            <div class="text-blue-200 text-sm mb-5">per bulan</div>
                        @elseif($minDaily)
                            <div class="text-3xl font-extrabold mb-0.5">Rp {{ number_format($minDaily, 0, ',', '.') }}</div>
                            <div class="text-blue-200 text-sm mb-5">per hari</div>
                        @else
                            <div class="text-2xl font-extrabold mb-5">Hubungi kami</div>
                        @endif

                        <div class="bg-white/10 rounded-2xl p-3 mb-5 text-sm">
                            <div class="flex justify-between mb-2">
                                <span class="text-blue-200">Kamar tersedia</span>
                                <span class="font-bold">{{ $availableTotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-200">Tipe kamar</span>
                                <span class="font-bold">{{ $roomTypes->count() }}</span>
                            </div>
                        </div>

                        @if($availableTotal > 0)
                            <a href="#kamar"
                               class="block w-full bg-white text-primary-700 text-center font-bold py-3.5 rounded-2xl hover:bg-blue-50 transition-all text-sm shadow-md">
                                Lihat Kamar & Booking
                            </a>
                        @else
                            <div class="w-full bg-white/20 text-white text-center font-semibold py-3.5 rounded-2xl text-sm">
                                Semua Kamar Penuh
                            </div>
                        @endif
                    </div>

                    {{-- WA Contact --}}
                    @if(setting('contact_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}?text={{ urlencode('Halo, saya tertarik dengan kamar di ' . $property->name . '. Apakah masih tersedia?') }}"
                       target="_blank"
                       class="flex items-center justify-center gap-3 w-full bg-green-500 text-white font-bold py-4 rounded-2xl hover:bg-green-600 transition-all shadow-lg shadow-green-500/20 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Tanya via WhatsApp
                    </a>
                    @endif

                    {{-- Navigation --}}
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 text-sm">
                        <div class="font-semibold text-slate-700 mb-3 text-xs uppercase tracking-wider">Navigasi Cepat</div>
                        <div class="space-y-1">
                            <a href="#kamar" class="flex items-center gap-2 text-slate-600 hover:text-primary-600 py-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"/></svg>
                                Tipe Kamar
                            </a>
                            @if($testimonials->isNotEmpty())
                            <a href="#testimoni" class="flex items-center gap-2 text-slate-600 hover:text-primary-600 py-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                Testimoni
                            </a>
                            @endif
                            @if($faqs->isNotEmpty())
                            <a href="#faq" class="flex items-center gap-2 text-slate-600 hover:text-primary-600 py-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                FAQ
                            </a>
                            @endif
                            <a href="#kontak" class="flex items-center gap-2 text-slate-600 hover:text-primary-600 py-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                Kontak
                            </a>
                        </div>
                    </div>

                    {{-- Back to all properties --}}
                    <a href="{{ route('landing.home') }}"
                       class="flex items-center justify-center gap-2 w-full bg-slate-100 text-slate-600 font-semibold py-3 rounded-2xl hover:bg-slate-200 transition-all text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Kembali ke Semua Properti
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Mobile sticky booking bar --}}
@if($availableTotal > 0)
<div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 px-4 py-3 flex gap-3 shadow-2xl">
    <a href="#kamar"
       class="flex-1 bg-primary-600 text-white text-center font-bold py-3.5 rounded-2xl text-sm">
        Booking Kamar ({{ $availableTotal }} tersedia)
    </a>
    @if(setting('contact_whatsapp'))
    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}?text={{ urlencode('Halo, saya tertarik dengan kamar di ' . $property->name . '. Apakah masih tersedia?') }}"
       target="_blank"
       class="w-14 bg-green-500 text-white rounded-2xl flex items-center justify-center">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
    @endif
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Swiper === 'undefined') return;

    @if(!empty($property->photos) && count($property->photos) > 1)
    new Swiper('.gallerySwiper', {
        slidesPerView: 1,
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        pagination: { el: '.swiper-pagination', clickable: true },
        loop: true,
        autoplay: { delay: 5000, disableOnInteraction: false },
    });
    @endif

    @if($testimonials->isNotEmpty())
    new Swiper('.propTestiSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: { el: '.swiper-pagination', clickable: true },
        breakpoints: { 640: { slidesPerView: 2 } },
        autoplay: { delay: 4000, disableOnInteraction: false },
        loop: {{ $testimonials->count() > 2 ? 'true' : 'false' }},
    });
    @endif
});
</script>

@endsection
