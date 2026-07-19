<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Kos Kosan Pro</title>
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
    @vite(['resources/css/filament/admin/theme.css'])
    @filamentStyles
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .fi-simple-main { background: transparent !important; box-shadow: none !important; padding: 0 !important; }
        .fi-simple-page { max-width: 100% !important; }
        .fi-input { border-radius: 12px !important; padding: 12px 16px !important; border: 1.5px solid #d1d5db !important; }
        .fi-input:focus { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,0.12) !important; }
        .fi-btn-primary { background: linear-gradient(135deg, #2563eb, #1d4ed8) !important; border-radius: 12px !important; padding: 12px !important; font-weight: 700 !important; box-shadow: 0 4px 12px rgba(37,99,235,0.3) !important; }
        .fi-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(37,99,235,0.4) !important; }
        @media (max-width: 1023px) {
            .hero-left { display: none; }
        }
    </style>
</head>
<body class="h-full bg-white">

<div class="min-h-screen flex">

    {{-- LEFT: Hero Branded --}}
    <div class="hero-left w-1/2 relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 p-16 flex flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.12) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(99,102,241,.15) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-32 -right-32 text-[25rem] opacity-[0.06] select-none">🏢</div>

        <div class="relative">
            <a href="/" class="inline-flex items-center gap-3.5 text-white no-underline group">
                <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">🏢</div>
                <div>
                    <span class="block font-extrabold text-2xl tracking-tight">Kos Kosan Pro</span>
                    <span class="block text-xs text-blue-200/60 font-medium">Manajemen Kos Modern</span>
                </div>
            </a>
        </div>

        <div class="relative text-white">
            <h1 class="text-6xl font-extrabold leading-[1.1] mb-6 tracking-tight">
                Kelola Bisnis<br>Kos Kosan<br><span class="text-blue-300">Lebih Mudah</span>
            </h1>
            <p class="text-blue-100/80 text-lg leading-relaxed mb-10 max-w-lg">
                Satu dashboard untuk mengelola properti, penyewa, invoice, laporan keuangan, dan operasional kos kosan Anda.
            </p>
            <div class="grid grid-cols-3 gap-4 max-w-md">
                <div class="bg-white/10 backdrop-blur rounded-2xl p-5 text-center border border-white/10">
                    <div class="text-3xl mb-2">📊</div>
                    <p class="text-xs text-blue-100 font-semibold leading-tight">Laporan<br>Real-time</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-2xl p-5 text-center border border-white/10">
                    <div class="text-3xl mb-2">⚡</div>
                    <p class="text-xs text-blue-100 font-semibold leading-tight">Invoice<br>Otomatis</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-2xl p-5 text-center border border-white/10">
                    <div class="text-3xl mb-2">🔐</div>
                    <p class="text-xs text-blue-100 font-semibold leading-tight">Portal<br>Penyewa</p>
                </div>
            </div>
        </div>

        <div class="relative text-blue-200/50 text-xs font-medium">
            &copy; {{ date('Y') }} Kos Kosan Pro &middot; v1.0
        </div>
    </div>

    {{-- RIGHT: Login Form --}}
    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 lg:p-16 bg-white">
        <div class="w-full max-w-[420px]">
            <div class="lg:hidden mb-10 text-center">
                <span class="text-5xl">🏢</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-3">Kos Kosan Pro</h2>
                <p class="text-slate-500 text-sm mt-1">Manajemen Kos Modern</p>
            </div>

            <div class="mb-2">
                <h1 class="text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">Masuk</h1>
                <p class="text-slate-500 mt-2 text-sm">
                    Belum punya akun?
                    <a href="/admin/register" class="text-primary-600 font-semibold hover:underline">Daftar gratis</a>
                </p>
            </div>

            {{-- Filament Login Form --}}
            {{ $slot }}

            {{-- Demo Accounts --}}
            <div class="mt-6 bg-slate-50 border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg">🧪</span>
                    <span class="font-bold text-slate-800 text-sm">Akun Demo</span>
                </div>
                <div class="space-y-1.5 text-slate-600 text-xs font-mono">
                    <div class="flex items-center gap-2">
                        <span class="bg-primary-100 text-primary-700 px-2 py-0.5 rounded text-[10px] font-bold">ADMIN</span>
                        <span>admin@kos.test / password</span>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 mt-3">Hubungi WA 0812-9605-2010 untuk bantuan akses.</p>
            </div>
        </div>
    </div>
</div>

@filamentScripts
@livewireScripts
</body>
</html>
