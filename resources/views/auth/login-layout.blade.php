<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ setting('app_name', 'Kos Manager') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, ::before, ::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, sans-serif; -webkit-font-smoothing: antialiased; }
    </style>
    @vite(['resources/css/filament/admin/theme.css'])
    @filamentStyles
    @livewireStyles
</head>
<body class="h-full bg-white">

<div class="min-h-screen grid lg:grid-cols-2">

    {{-- Left: Hero Branded --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 p-12 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.15) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(99,102,241,.2) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-10">🏠</div>

        <div class="relative">
            <a href="/" class="flex items-center gap-3 text-white no-underline">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center text-2xl">🏠</div>
                <span class="font-extrabold text-2xl">{{ setting('app_name', 'Kos Manager') }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="text-5xl font-extrabold leading-tight mb-4">Kelola Kos Jadi Lebih Mudah</h2>
            <p class="text-blue-100 text-lg leading-relaxed mb-8 max-w-md">Satu dashboard untuk semua: properti, penghuni, invoice, dan laporan keuangan.</p>
            <div class="grid grid-cols-3 gap-4 max-w-md">
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <div class="text-2xl mb-1">📊</div>
                    <p class="text-xs text-blue-100 font-medium">Laporan Real-time</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <div class="text-2xl mb-1">💳</div>
                    <p class="text-xs text-blue-100 font-medium">Auto Invoice</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <div class="text-2xl mb-1">📱</div>
                    <p class="text-xs text-blue-100 font-medium">Portal Penghuni</p>
                </div>
            </div>
        </div>

        <div class="relative text-blue-200/70 text-xs">&copy; {{ date('Y') }} {{ setting('app_name', 'Kos Manager') }}</div>
    </div>

    {{-- Right: Login Form --}}
    <div class="flex flex-col items-center justify-center p-8 lg:p-16 bg-white">
        <div class="w-full max-w-md">
            {{ $slot }}

            <div class="mt-8 bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                <div class="font-semibold text-slate-800 mb-2">🧪 Akun Demo</div>
                <div class="space-y-1 text-slate-600 text-xs font-mono">
                    <div><span class="font-bold">Owner:</span> owner@kos.test / password</div>
                    <div><span class="font-bold">Staff:</span> staff@kos.test / password</div>
                    <div><span class="font-bold">Viewer:</span> viewer@kos.test / password</div>
                </div>
            </div>
        </div>
    </div>
</div>

@filamentScripts
@livewireScripts
</body>
</html>
