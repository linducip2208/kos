<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Kirim Pesan Massal</h2>
            <p class="text-sm text-gray-500 mb-4">Pesan tercatat di log WhatsApp. Jika gateway nonaktif, pengiriman ditandai gagal tanpa mengirim sungguhan.</p>
            {{ $this->form }}
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Riwayat Blast Terakhir</h2>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
