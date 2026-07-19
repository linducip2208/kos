<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ setting('app_name', 'Kos Manager') }}</title>
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
</head>
<body class="font-sans antialiased bg-white">

<div class="min-h-screen grid lg:grid-cols-2">

    {{-- Left: Hero Branded --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-blue-600 via-blue-700 to-slate-900 p-12 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,.15) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(99,102,241,.2) 0%, transparent 50%)"></div>
        <div class="absolute -bottom-20 -right-20 text-[20rem] opacity-10">🏠</div>

        <div class="relative">
            <a href="/" class="flex items-center gap-3 text-white">
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
    <div class="flex items-center justify-center p-8 lg:p-16">
        <div class="w-full max-w-md">
            <div class="lg:hidden mb-8 text-center">
                <span class="text-4xl">🏠</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-2">{{ setting('app_name', 'Kos Manager') }}</h2>
            </div>

            <h1 class="text-4xl font-extrabold text-slate-900 mb-2">Masuk</h1>
            <p class="text-slate-500 mb-8">Belum punya akun? <a href="/admin/register" class="text-primary-600 font-semibold hover:underline">Daftar gratis</a></p>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6 text-sm">
                {{ $errors->first('email') }}
            </div>
            @endif

            <form wire:submit="authenticate" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" wire:model="data.email" required autofocus autocomplete="email"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-3 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" wire:model="data.password" required autocomplete="current-password"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-3 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-slate-600">
                        <input type="checkbox" wire:model="data.remember" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        Ingat saya
                    </label>
                    <a href="{{ route('filament.admin.auth.password-reset.request') ?? '/admin/password/reset' }}" class="text-primary-600 font-medium hover:underline">Lupa password?</a>
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/25 hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5 transition-all">
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="flex items-center gap-4 my-8">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs text-slate-400 font-medium">atau</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm">
                <div class="font-semibold text-slate-800 mb-2">Akun Demo</div>
                <div class="space-y-1 text-slate-600 text-xs font-mono">
                    <div><span class="font-bold">Owner:</span> owner@kos.test / password</div>
                    <div><span class="font-bold">Staff:</span> staff@kos.test / password</div>
                    <div><span class="font-bold">Viewer:</span> viewer@kos.test / password</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
