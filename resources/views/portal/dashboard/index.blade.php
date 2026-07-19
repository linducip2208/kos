@extends('portal.layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- Active Lease Card --}}
@if($activeLease)
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <h2 class="font-semibold text-gray-700 mb-3">Kamar Anda</h2>
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <span class="text-gray-500">Properti</span>
            <div class="font-medium">{{ $activeLease->room->property->name ?? '-' }}</div>
        </div>
        <div>
            <span class="text-gray-500">No. Kamar</span>
            <div class="font-medium">{{ $activeLease->room->room_number ?? '-' }}</div>
        </div>
        <div>
            <span class="text-gray-500">Mulai Kontrak</span>
            <div class="font-medium">{{ $activeLease->start_date?->format('d M Y') }}</div>
        </div>
        <div>
            <span class="text-gray-500">Berakhir</span>
            <div class="font-medium {{ $activeLease->end_date?->diffInDays(now()) < 30 ? 'text-red-600' : '' }}">
                {{ $activeLease->end_date?->format('d M Y') }}
                @if($activeLease->end_date && $activeLease->end_date->isFuture())
                    <span class="text-xs text-gray-400">({{ $activeLease->end_date->diffForHumans() }})</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <div class="text-2xl font-bold text-red-500">{{ $unpaidInvoices->count() }}</div>
        <div class="text-xs text-gray-500 mt-1">Tagihan Belum Bayar</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <div class="text-2xl font-bold text-orange-500">{{ $openMaintenance }}</div>
        <div class="text-xs text-gray-500 mt-1">Laporan Maintenance</div>
    </div>
</div>

{{-- Unpaid Invoices --}}
@if($unpaidInvoices->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <div class="flex justify-between items-center mb-3">
        <h2 class="font-semibold text-gray-700">Tagihan Menunggu Pembayaran</h2>
        <a href="{{ route('portal.invoices.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
    </div>
    <div class="space-y-3">
        @foreach($unpaidInvoices as $inv)
        <div class="flex justify-between items-center py-2 border-b last:border-0">
            <div>
                <div class="font-medium text-sm">{{ $inv->invoice_number }}</div>
                <div class="text-xs text-gray-500">Jatuh tempo: {{ $inv->due_date?->format('d M Y') }}</div>
            </div>
            <div class="text-right">
                <div class="font-semibold text-sm">Rp {{ number_format($inv->total, 0, ',', '.') }}</div>
                <span class="text-xs px-1.5 py-0.5 rounded {{ $inv->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $inv->status === 'overdue' ? 'Lewat Jatuh Tempo' : 'Belum Dibayar' }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Quick Links --}}
<div class="grid grid-cols-2 gap-3">
    <a href="{{ route('portal.invoices.index') }}" class="bg-blue-50 rounded-xl p-4 text-center hover:bg-blue-100 transition">
        <div class="text-2xl mb-1">📄</div>
        <div class="text-sm font-medium text-blue-700">Lihat Tagihan</div>
    </a>
    <a href="{{ route('portal.maintenance.create') }}" class="bg-orange-50 rounded-xl p-4 text-center hover:bg-orange-100 transition">
        <div class="text-2xl mb-1">🔧</div>
        <div class="text-sm font-medium text-orange-700">Lapor Kerusakan</div>
    </a>
</div>

@endsection
