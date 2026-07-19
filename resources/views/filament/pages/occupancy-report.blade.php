@extends('filament::layouts.app')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold text-slate-900 mb-6">Laporan Okupansi Kamar</h2>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-sm text-slate-500">Total Kamar</div>
            <div class="text-3xl font-extrabold text-slate-900">{{ $totalRooms }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-sm text-slate-500">Terisi</div>
            <div class="text-3xl font-extrabold text-green-600">{{ $occupied }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-sm text-slate-500">Tersedia</div>
            <div class="text-3xl font-extrabold text-blue-600">{{ $available }}</div>
        </div>
        <div class="bg-white rounded-xl p-5 border border-slate-200">
            <div class="text-sm text-slate-500">Maintenance</div>
            <div class="text-3xl font-extrabold text-amber-600">{{ $maintenance }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Properti</th>
                    <th class="text-center px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Total</th>
                    <th class="text-center px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Terisi</th>
                    <th class="text-center px-5 py-3 font-semibold text-slate-600 uppercase text-xs tracking-wider">Okupansi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($properties as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $p['name'] }}</td>
                    <td class="px-5 py-3 text-center">{{ $p['total'] }}</td>
                    <td class="px-5 py-3 text-center">{{ $p['occupied'] }}</td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-24 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full" style="width:{{ $p['rate'] }}%"></div>
                            </div>
                            <span class="text-xs font-semibold {{ $p['rate'] > 80 ? 'text-green-600' : ($p['rate'] > 50 ? 'text-blue-600' : 'text-amber-600') }}">{{ $p['rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
