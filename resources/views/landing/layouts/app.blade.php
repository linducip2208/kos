<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', setting('app_name', 'Kos Manager'))</title>
    <meta name="description" content="@yield('description', 'Temukan hunian kos terbaik — nyaman, terpercaya, proses mudah.')">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: { DEFAULT: '#2563eb', 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    },
                }
            }
        }
    </script>

    {{-- Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

    {{-- AOS --}}
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>

    {{-- Alpine --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        .gradient-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 40%, #1d4ed8 70%, #2563eb 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #a78bfa, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .glass-white {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .card-premium {
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease;
        }
        .card-premium:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px rgba(37,99,235,.15);
        }
        .nav-scrolled {
            background: rgba(255,255,255,0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 1px 20px rgba(0,0,0,.08);
        }
        .swiper-pagination-bullet-active { background: #2563eb !important; }
        .price-badge {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
        }
        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }
        @keyframes fade-up {
            from { opacity:0; transform: translateY(20px); }
            to   { opacity:1; transform: translateY(0); }
        }
        .fade-up { animation: fade-up .6s ease forwards; }
    </style>
</head>

<body class="bg-slate-50 font-sans text-slate-800 antialiased" x-data>

{{-- ═══════════════════ NAVBAR ═══════════════════ --}}
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
     x-data="{ scrolled: false, open: false }"
     x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 40)"
     :class="scrolled ? 'nav-scrolled' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16 md:h-18">

        <a href="{{ route('landing.home') }}" class="flex items-center gap-2.5 font-extrabold text-xl"
           :class="scrolled ? 'text-primary-700' : 'text-white'">
            @if(setting('app_logo'))
                <img src="{{ asset('storage/' . setting('app_logo')) }}" class="h-8 w-auto" alt="logo">
            @else
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
            @endif
            <span>{{ setting('app_name', 'Kos Manager') }}</span>
        </a>

        {{-- Desktop Nav --}}
        <div class="hidden md:flex items-center gap-6">
            <a href="{{ route('landing.home') }}"
               class="text-sm font-medium transition-colors"
               :class="scrolled ? 'text-slate-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
                Beranda
            </a>
            <a href="#fasilitas"
               class="text-sm font-medium transition-colors"
               :class="scrolled ? 'text-slate-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
                Fasilitas
            </a>
            <a href="#kontak"
               class="text-sm font-medium transition-colors"
               :class="scrolled ? 'text-slate-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
                Kontak
            </a>
            <a href="{{ route('portal.login') }}"
               class="text-sm font-medium transition-colors"
               :class="scrolled ? 'text-slate-600 hover:text-primary-600' : 'text-white/80 hover:text-white'">
                Portal Penyewa
            </a>
            <a href="{{ route('portal.login') }}"
               class="text-sm bg-primary-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-primary-700 transition-all shadow-md shadow-blue-500/30">
                Login
            </a>
        </div>

        {{-- Mobile menu button --}}
        <button @click="open = !open" class="md:hidden p-2 rounded-lg"
                :class="scrolled ? 'text-slate-700' : 'text-white'">
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="open" x-cloak x-transition
         class="md:hidden bg-white border-t border-slate-100 px-4 py-4 space-y-3 shadow-xl">
        <a href="{{ route('landing.home') }}" class="block text-sm font-medium text-slate-700 py-2">Beranda</a>
        <a href="#fasilitas" @click="open=false" class="block text-sm font-medium text-slate-700 py-2">Fasilitas</a>
        <a href="#kontak" @click="open=false" class="block text-sm font-medium text-slate-700 py-2">Kontak</a>
        <a href="{{ route('portal.login') }}" class="block text-sm font-medium text-slate-700 py-2">Portal Penyewa</a>
        <a href="{{ route('portal.login') }}" class="block text-center bg-primary-600 text-white px-4 py-2.5 rounded-xl font-semibold text-sm">Login Portal</a>
    </div>
</nav>

{{-- PAGE CONTENT --}}
@yield('content')

{{-- ═══════════════════ FOOTER ═══════════════════ --}}
<footer class="bg-slate-900 text-slate-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 font-extrabold text-xl text-white mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                    </div>
                    {{ setting('app_name', 'Kos Manager') }}
                </div>
                <p class="text-sm leading-relaxed text-slate-400 max-w-xs">
                    Hunian kos terpercaya dengan fasilitas lengkap, proses booking mudah, dan pengelolaan profesional.
                </p>
                @if(setting('contact_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 mt-5 bg-green-500 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-green-600 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp Kami
                    </a>
                @endif
            </div>

            <div>
                <h4 class="text-white font-semibold mb-4">Navigasi</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('landing.home') }}" class="hover:text-white transition-colors">Beranda</a></li>
                    <li><a href="#kamar" class="hover:text-white transition-colors">Pilihan Kamar</a></li>
                    <li><a href="#testimoni" class="hover:text-white transition-colors">Testimoni</a></li>
                    <li><a href="#faq" class="hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="{{ route('portal.login') }}" class="hover:text-white transition-colors">Portal Penyewa</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-semibold mb-4">Kontak</h4>
                <ul class="space-y-2.5 text-sm">
                    @if(setting('contact_phone'))
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ setting('contact_phone') }}
                        </li>
                    @endif
                    @if(setting('contact_email'))
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ setting('contact_email') }}
                        </li>
                    @endif
                    @if(setting('contact_address'))
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ setting('contact_address') }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} {{ setting('app_name', 'Kos Manager') }}. All rights reserved.</p>
            <p>Dikelola dengan <span class="text-red-400">♥</span> menggunakan sistem manajemen kos terbaik.</p>
        </div>
    </div>
</footer>

{{-- Back to top --}}
<button x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => show = window.scrollY > 400)"
        x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        @click="window.scrollTo({top:0,behavior:'smooth'})"
        class="fixed bottom-24 right-6 w-11 h-11 bg-slate-800/80 backdrop-blur text-white rounded-full shadow-lg flex items-center justify-center hover:bg-primary-600 transition-colors z-40">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
    </svg>
</button>

{{-- WA Floating --}}
@if(setting('contact_whatsapp'))
<a href="https://wa.me/{{ preg_replace('/\D/', '', setting('contact_whatsapp')) }}"
   target="_blank"
   class="fixed bottom-6 right-6 w-14 h-14 bg-green-500 text-white rounded-full shadow-xl shadow-green-500/40 flex items-center justify-center hover:bg-green-600 hover:scale-110 transition-all z-50"
   title="Chat WhatsApp">
    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
</a>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof AOS !== 'undefined') AOS.init({ duration: 700, once: true, offset: 80 });
    });
</script>

{{-- ════════════════════════════ PURCHASE POPUP ════════════════════════════ --}}
{{-- Untuk menghilangkan popup ini secara permanen, hapus seluruh blok antara komentar PURCHASE POPUP dan /PURCHASE POPUP --}}
<style>
    @keyframes purchase-popup-in {
        from { opacity: 0; transform: translateY(20px) scale(.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    #purchase-popup { animation: purchase-popup-in .35s ease-out; }
    @media print { #purchase-popup { display: none !important; } }
</style>
<div id="purchase-popup-wrapper"></div>
<script>
(function () {
    'use strict';
    var KEY = 'purchasePopupDismissed';
    var COOLDOWN_MS = 7 * 24 * 60 * 60 * 1000; // 7 hari
    var SHOW_AFTER_MS = 5000;

    function shouldShow() {
        try {
            var until = parseInt(localStorage.getItem(KEY) || '0', 10);
            return Date.now() > until;
        } catch (e) { return true; }
    }
    function dismiss() {
        try { localStorage.setItem(KEY, String(Date.now() + COOLDOWN_MS)); } catch (e) {}
        var el = document.getElementById('purchase-popup');
        if (el) { el.style.opacity = '0'; el.style.transform = 'translateY(20px)'; setTimeout(function(){ el.remove(); }, 300); }
    }
    function render() {
        if (!shouldShow()) return;
        var html =
                        '<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-12 mt-16">' +
              '<div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">' +
                '<h2 class="text-3xl font-extrabold mb-4">Butuh Aplikasi Manajemen Kos Kosan?</h2>' +
                '<p class="text-blue-100 mb-8 max-w-2xl mx-auto">Source code aplikasi Kos Kosan Pro siap pakai. Fitur lengkap: manajemen properti, booking, invoice otomatis, laporan keuangan, portal penghuni. Bisa dikustomisasi!</p>' +
                '<a href="https://wa.me/6281296052010" target="_blank" class="inline-block bg-white text-primary-700 px-8 py-3 rounded-xl font-bold text-lg hover:bg-blue-50 transition-colors">Beli Source Code</a>' +
                '<p class="text-blue-200 text-sm mt-4">Atau hubungi WhatsApp kami untuk demo gratis</p>' +
              '</div>' +
            '</div>' +
              '<div style="background:linear-gradient(135deg,#10b981,#0d9488);" class="px-5 py-4 flex items-start justify-between gap-3">' +
                '<div class="flex items-center gap-3">' +
                  '<div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl">💼</div>' +
                  '<div>' +
                    '<div class="text-white font-bold leading-tight">Mau aplikasi seperti ini?</div>' +
                    '<div class="text-emerald-50 text-xs">Hubungi pemilik untuk pembelian</div>' +
                  '</div>' +
                '</div>' +
                '<button id="purchase-popup-close" aria-label="Tutup" class="text-white/80 hover:text-white text-xl leading-none -mr-1 -mt-1 px-2">&times;</button>' +
              '</div>' +
              '<div class="p-5">' +
                '<div class="text-xs text-slate-500 mb-1">WhatsApp</div>' +
                '<div class="text-2xl font-extrabold text-slate-800 mb-3 tracking-tight">081296052010</div>' +
                '<a href="https://wa.me/6281296052010?text=Halo%2C%20saya%20tertarik%20untuk%20membeli%20aplikasi%20Kos%20Kosan%20Pro" target="_blank" rel="noopener"' +
                   ' class="block text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl transition">Chat WhatsApp Sekarang</a>' +
                '<p style="font-size:11px;" class="text-slate-400 mt-3 mb-0 text-center">Tutup untuk sembunyikan 7 hari.</p>' +
              '</div>' +
            '</div>';
        var wrap = document.getElementById('purchase-popup-wrapper');
        if (!wrap) return;
        wrap.innerHTML = html;
        var btn = document.getElementById('purchase-popup-close');
        if (btn) btn.addEventListener('click', dismiss);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(render, SHOW_AFTER_MS); });
    } else {
        setTimeout(render, SHOW_AFTER_MS);
    }
})();
</script>
{{-- /PURCHASE POPUP --}}

</body>
</html>
