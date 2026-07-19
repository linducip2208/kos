<x-filament-panels::page>
@php
    $info    = $this->getLicenseInfo();
    $status  = $info['status'] ?? 'inactive';
    $isActive = $status === 'active';
@endphp

<div class="space-y-5">

    {{-- Status Card --}}
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="font-bold text-gray-800 text-base mb-1">Status Lisensi</h3>
                @if($isActive)
                    <span class="inline-flex items-center gap-1.5 text-sm bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
                        Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-sm bg-red-100 text-red-600 px-3 py-1 rounded-full font-semibold">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>
                        {{ ucfirst($status) }}
                    </span>
                @endif
            </div>
            <div class="flex gap-2 flex-wrap">
                <button wire:click="revalidate" class="text-sm bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg font-medium transition">
                    Revalidasi
                </button>
                <button wire:click="checkUpdate" class="text-sm bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg font-medium transition">
                    Cek Pembaruan
                </button>
            </div>
        </div>

        @if($isActive)
        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div>
                <span class="text-gray-500">Activation Key</span>
                <p class="font-mono text-gray-800 mt-0.5 text-xs break-all">{{ $info['key'] }}</p>
            </div>
            @if($info['product'])
            <div>
                <span class="text-gray-500">Produk</span>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $info['product'] }}</p>
            </div>
            @endif
            @if($info['version'])
            <div>
                <span class="text-gray-500">Versi</span>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $info['version'] }}</p>
            </div>
            @endif
            @if($info['type'])
            <div>
                <span class="text-gray-500">Tipe Lisensi</span>
                <p class="font-semibold text-gray-800 mt-0.5 capitalize">{{ $info['type'] }}</p>
            </div>
            @endif
            @if($info['activated_at'])
            <div>
                <span class="text-gray-500">Diaktifkan</span>
                <p class="font-semibold text-gray-800 mt-0.5">{{ \Carbon\Carbon::parse($info['activated_at'])->format('d M Y') }}</p>
            </div>
            @endif
            @if($info['expires_at'])
            <div>
                <span class="text-gray-500">Masa Berlaku</span>
                <p class="font-semibold text-gray-800 mt-0.5">{{ \Carbon\Carbon::parse($info['expires_at'])->format('d M Y') }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Activate Form --}}
    @unless($isActive)
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h3 class="font-bold text-gray-800 text-base mb-3">Aktifkan Lisensi</h3>
        <div class="flex gap-3">
            <input
                wire:model="activationKey"
                type="text"
                placeholder="XXXXX-XXXXX-XXXXX-XXXXX"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button wire:click="activate" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition whitespace-nowrap">
                Aktifkan
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            Beli lisensi di <a href="https://whitelabel.co.id" target="_blank" class="text-blue-500 underline">whitelabel.co.id</a>.
            Atau jalankan: <code class="bg-gray-100 px-1 rounded">php artisan license:activate KEY</code>
        </p>
    </div>
    @endunless

    {{-- Revoke --}}
    @if($isActive)
    <div class="bg-white border border-red-100 rounded-xl p-5 shadow-sm">
        <h3 class="font-bold text-red-700 text-base mb-1">Cabut Lisensi</h3>
        <p class="text-sm text-gray-500 mb-3">
            Gunakan sebelum pindah domain. Setelah dicabut, aplikasi memerlukan aktivasi ulang.
        </p>
        <button
            wire:click="revoke"
            wire:confirm="Yakin ingin mencabut lisensi? Aplikasi tidak bisa digunakan sampai diaktifkan ulang."
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition"
        >
            Cabut Lisensi
        </button>
    </div>
    @endif

</div>
</x-filament-panels::page>
