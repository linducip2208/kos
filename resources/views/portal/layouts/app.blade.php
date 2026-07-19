<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Penyewa') — {{ setting('app_name', 'Kos Manager') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media (max-width: 640px) {
            .portal-card { padding: 14px; border-radius: 10px; }
            .portal-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .portal-btn { min-height: 38px; width: 100%; justify-content: center; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ navOpen: false }">

<nav class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-4xl mx-auto px-4 flex items-center justify-between h-14">
        <a href="{{ route('portal.dashboard') }}" class="font-bold text-blue-600 text-lg">
            🏠 {{ setting('app_name', 'Kos Manager') }}
        </a>

        {{-- Desktop nav --}}
        <div class="hidden md:flex items-center gap-4">
            <span class="text-sm text-gray-600">{{ auth('portal')->user()->name ?? 'Penghuni' }}</span>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-500 hover:underline">Keluar</button>
            </form>
        </div>

        {{-- Mobile hamburger --}}
        <button @click="navOpen = !navOpen" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-gray-100">
            <svg x-show="!navOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="navOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile nav dropdown --}}
    <div x-show="navOpen" x-cloak x-transition class="md:hidden bg-white border-t border-gray-100 px-4 py-3 space-y-2">
        <div class="text-sm text-gray-600 py-1">👤 {{ auth('portal')->user()->name ?? 'Penghuni' }}</div>
        <form method="POST" action="{{ route('portal.logout') }}">
            @csrf
            <button type="submit" class="text-sm text-red-500 hover:underline py-1">Keluar</button>
        </form>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-4 md:py-6">

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Nav tabs — scrollable on mobile --}}
    <div class="flex gap-1.5 mb-6 overflow-x-auto -mx-1 px-1 pb-1" style="-webkit-overflow-scrolling:touch">
        <a href="{{ route('portal.dashboard') }}" class="portal-btn px-3 py-1.5 rounded text-sm whitespace-nowrap {{ request()->routeIs('portal.dashboard') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">Dashboard</a>
        <a href="{{ route('portal.invoices.index') }}" class="portal-btn px-3 py-1.5 rounded text-sm whitespace-nowrap {{ request()->routeIs('portal.invoices*') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">Tagihan</a>
        <a href="{{ route('portal.maintenance.index') }}" class="portal-btn px-3 py-1.5 rounded text-sm whitespace-nowrap {{ request()->routeIs('portal.maintenance*') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">Maintenance</a>
        <a href="{{ route('portal.profile.edit') }}" class="portal-btn px-3 py-1.5 rounded text-sm whitespace-nowrap {{ request()->routeIs('portal.profile*') ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">Profil</a>
    </div>

    @yield('content')
</div>

</body>
</html>
