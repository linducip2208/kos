<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seo['title'] ?? 'Kos Manager' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    <meta property="og:title" content="{{ $seo['title'] ?? '' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    <meta property="og:url" content="{{ $seo['canonical'] ?? url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    @php
        $ldJson = [];
        if (isset($properties) && $properties->count()) {
            foreach ($properties as $i => $p) {
                $ldJson[] = [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $p->name,
                    'url' => route('landing.property', $p),
                ];
            }
        }
    @endphp
    @if(count($ldJson))
    <script type="application/ld+json">
    {!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $ldJson], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'] },
                    colors: { primary: { DEFAULT: '#2563eb', 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' } },
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans antialiased">

<header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
        <a href="/" class="flex items-center gap-2.5 font-extrabold text-xl text-primary-700">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
            </div>
            <span>Kos Kosan Pro</span>
        </a>
        <a href="https://wa.me/6281296052010" target="_blank" class="bg-green-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-600 transition-colors">WhatsApp</a>
    </div>
</header>

<main>
    @yield('content')
</main>

<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 mt-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl font-extrabold mb-4">Butuh Aplikasi Manajemen Kos Kosan?</h2>
        <p class="text-blue-100 mb-8 max-w-2xl mx-auto">Source code aplikasi Kos Kosan Pro siap pakai. Fitur lengkap: manajemen properti, booking, invoice otomatis, laporan keuangan, portal penghuni. Bisa dikustomisasi!</p>
        <a href="{{ route('pseo.beli') }}" class="inline-block bg-white text-primary-700 px-8 py-3 rounded-xl font-bold text-lg hover:bg-blue-50 transition-colors">Beli Source Code — Rp 2.5jt</a>
        <p class="text-blue-200 text-sm mt-4">Atau WA 0812-9605-2010 untuk demo gratis</p>
    </div>
</div>

<footer class="bg-slate-900 text-slate-400 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center text-sm">
        &copy; {{ date('Y') }} Kos Kosan Pro. All rights reserved.
    </div>
</footer>

</body>
</html>
