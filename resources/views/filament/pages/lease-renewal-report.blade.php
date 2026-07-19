<div class="p-6">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">Laporan Perpanjangan Kontrak</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 border border-red-200 bg-red-50">
            <div class="text-sm text-red-600 font-semibold">Sudah Melewati Tanggal</div>
            <div class="text-3xl font-extrabold text-red-700">{{ $expired->count() }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-amber-200 bg-amber-50">
            <div class="text-sm text-amber-600 font-semibold">Akan Habis ≤7 Hari</div>
            <div class="text-3xl font-extrabold text-amber-700">{{ $expiring7->count() }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-blue-200 bg-blue-50">
            <div class="text-sm text-blue-600 font-semibold">Akan Habis ≤30 Hari</div>
            <div class="text-3xl font-extrabold text-blue-700">{{ $expiring30->count() }}</div>
        </div>
    </div>

    @if($expired->count())
    <div class="mb-8">
        <h3 class="text-lg font-bold text-red-700 mb-3">🔴 Sudah Melewati Tanggal</h3>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">Penyewa</th>
                        <th class="px-4 py-2 text-left">Kamar</th>
                        <th class="px-4 py-2 text-left">Properti</th>
                        <th class="px-4 py-2">Berakhir</th>
                        <th class="px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($expired as $l)
                    <tr class="hover:bg-red-50">
                        <td class="px-4 py-2">{{ $l->occupant->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $l->room->room_number ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $l->room->property->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-center text-red-600">{{ $l->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-center"><span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">Expired</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div>
        <h3 class="text-lg font-bold text-slate-800 mb-3">🟡 Kontrak 30 Hari ke Depan</h3>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">Penyewa</th>
                        <th class="px-4 py-2 text-left">Kamar</th>
                        <th class="px-4 py-2 text-left">Properti</th>
                        <th class="px-4 py-2">Berakhir</th>
                        <th class="px-4 py-2">Sisa Hari</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($expiring30 as $l)
                    @php $remaining = now()->diffInDays($l->end_date, false); @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2">{{ $l->occupant->name ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $l->room->room_number ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $l->room->property->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">{{ $l->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="font-bold {{ $remaining <= 7 ? 'text-red-600' : 'text-amber-600' }}">{{ max($remaining, 0) }} hari</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada kontrak yang akan berakhir dalam 30 hari.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
