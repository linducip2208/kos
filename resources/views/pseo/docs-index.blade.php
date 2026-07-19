<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">

    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'], mono: ['JetBrains Mono', 'monospace'] },
                    colors: { primary: { DEFAULT: '#2563eb', 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' } },
                }
            }
        }
    </script>
    <style>
        @keyframes fadeSlideUp{0%{transform:translateY(30px);opacity:0}100%{transform:translateY(0);opacity:1}}
        .reveal{opacity:0;transform:translateY(30px);transition:opacity .6s,transform .6s cubic-bezier(.16,1,.3,1)}
        .reveal.visible{opacity:1;transform:translateY(0)}
        .browser-mock{border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.12);border:1px solid #e2e8f0}
        .browser-mock .bar{background:#f1f5f9;padding:10px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #e2e8f0}
        .browser-mock .bar .dots{display:flex;gap:6px}
        .browser-mock .bar .dots span{width:10px;height:10px;border-radius:50%}
        .browser-mock .bar .url{background:#e2e8f0;border-radius:6px;padding:4px 12px;font-size:11px;color:#64748b;flex:1;text-align:center;font-family:monospace}
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">

{{-- Jump Nav --}}
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center gap-6 h-14 overflow-x-auto text-sm font-medium">
        <a href="/" class="text-primary-600 font-extrabold shrink-0">{{ setting('app_name', 'Kos Manager') }}</a>
        <a href="#demo" class="text-slate-500 hover:text-primary-600 whitespace-nowrap transition-colors">Akun Demo</a>
        <a href="#struktur" class="text-slate-500 hover:text-primary-600 whitespace-nowrap transition-colors">Struktur Menu</a>
        <a href="#tutorial" class="text-slate-500 hover:text-primary-600 whitespace-nowrap transition-colors">Tutorial</a>
        <a href="#fitur" class="text-slate-500 hover:text-primary-600 whitespace-nowrap transition-colors">Fitur</a>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary-600 via-primary-700 to-indigo-800 py-16 md:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-white">Dokumentasi Kos Manager</h1>
        <p class="text-primary-100 mt-4 text-lg max-w-2xl mx-auto">Panduan lengkap penggunaan aplikasi manajemen kos. Dari setup awal hingga laporan keuangan.</p>
    </div>
</section>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 md:py-12 space-y-16 md:space-y-20">

    {{-- Demo Accounts --}}
    <section id="demo" class="reveal">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-8">Akun Demo</h2>
        <div class="overflow-x-auto">
            <table class="w-full bg-white rounded-xl shadow-sm border border-slate-200 text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Role</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Email</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Password</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Cakupan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-xs">
                    @foreach($demoAccounts as $acc)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ $acc['role'] }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $acc['email'] }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $acc['password'] }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $acc['scope'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Struktur Menu --}}
    <section id="struktur" class="reveal">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-8">Struktur Menu Admin</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                ['🏠', 'Properti & Kamar', 'Properti, Tipe Kamar, Kamar'],
                ['👤', 'Penghuni & Sewa', 'Penghuni, Sewa, Checklist Kamar'],
                ['💰', 'Keuangan', 'Invoice, Pembayaran, Meteran'],
                ['🔧', 'Operasional', 'Maintenance, Booking Request, E-Contract'],
                ['📊', 'Laporan', 'Laporan Keuangan, Okupansi'],
                ['⚙️', 'Pengaturan', 'Pengaturan Umum, Payment Gateway, License, Plugin, Theme'],
                ['📝', 'Marketing', 'Blog, Kategori Blog, FAQ, Testimonial, Kontak'],
            ] as $g)
            <div class="bg-white rounded-xl p-5 border border-slate-100">
                <div class="text-2xl mb-2">{{ $g[0] }}</div>
                <h4 class="font-bold text-slate-800">{{ $g[1] }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ $g[2] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Tutorial --}}
    <section id="tutorial" class="reveal">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-8">Tutorial Langkah Demi Langkah</h2>

        @foreach($tutorial as $phase)
        <div class="mb-10">
            <h3 class="text-xl font-bold text-primary-700 mb-4">{{ $phase['phase'] }}</h3>
            <div class="space-y-4">
                @foreach($phase['steps'] as $step)
                <div class="bg-white rounded-xl p-5 border border-slate-100 flex gap-4">
                    <div class="w-8 h-8 bg-primary-100 text-primary-700 rounded-full flex items-center justify-center font-bold text-sm shrink-0">{{ $step['num'] }}</div>
                    <div>
                        <h4 class="font-semibold text-slate-800">{{ $step['title'] }}</h4>
                        <p class="text-sm text-slate-500 mt-1"><strong>Tindakan:</strong> {{ $step['action'] }}</p>
                        <p class="text-sm text-slate-400 mt-1">{{ $step['detail'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </section>

    {{-- Fitur --}}
    <section id="fitur" class="reveal">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-8">Fitur Lengkap</h2>

        @foreach($features as $i => $f)
        <div class="flex flex-col {{ $i % 2 == 0 ? 'md:flex-row' : 'md:flex-row-reverse' }} gap-8 mb-16 items-center">
            <div class="flex-1">
                <div class="browser-mock">
                    <div class="bar">
                        <div class="dots"><span style="background:#ef4444"></span><span style="background:#f59e0b"></span><span style="background:#22c55e"></span></div>
                        <div class="url">kos.test/admin</div>
                    </div>
                    <div class="aspect-video bg-gradient-to-br from-primary-50 to-indigo-50 flex items-center justify-center p-8">
                        <div class="text-center">
                            <div class="text-4xl mb-4">
                                @switch($f['group'])
                                    @case('Master Data') 🏠 @break
                                    @case('Transaksi') 📋 @break
                                    @case('Keuangan') 💰 @break
                                    @case('Operasional') 🔧 @break
                                    @case('Laporan') 📊 @break
                                    @default 📦
                                @endswitch
                            </div>
                            <p class="text-lg font-bold text-slate-400">{{ $f['title'] }}</p>
                            <p class="text-xs text-slate-300 mt-1">Screenshot {{ $f['screenshot'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex-1">
                <span class="text-xs font-semibold text-primary-600 uppercase tracking-wider">{{ $f['group'] }}</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $f['title'] }}</h3>
                <p class="text-slate-500 mt-3 leading-relaxed">{{ $f['description'] }}</p>
                <ul class="mt-4 space-y-2">
                    @foreach($f['bullets'] as $b)
                    <li class="flex items-start gap-2 text-sm text-slate-600">
                        <svg class="w-5 h-5 text-primary-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $b }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endforeach
    </section>

    {{-- CTA --}}
    <section class="reveal bg-gradient-to-r from-primary-600 to-indigo-700 rounded-2xl p-10 md:p-16 text-center text-white">
        <h2 class="text-3xl font-extrabold">Siap Mengelola Kos Lebih Mudah?</h2>
        <p class="text-primary-100 mt-3 max-w-lg mx-auto">Coba demo gratis atau beli source code untuk digunakan selamanya.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8">
            <a href="/admin" class="bg-white text-primary-700 px-8 py-3 rounded-xl font-bold hover:bg-blue-50 transition-colors">Coba Demo</a>
            <a href="https://wa.me/6281296052010" class="bg-green-500 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-600 transition-colors">WhatsApp</a>
        </div>
    </section>
</div>

<footer class="bg-slate-900 text-slate-400 py-12 mt-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center text-sm">
        &copy; {{ date('Y') }} {{ setting('app_name', 'Kos Manager') }}. All rights reserved.
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
</script>
</body>
</html>
