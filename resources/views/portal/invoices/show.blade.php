@extends('portal.layouts.app')
@section('title', 'Detail Tagihan')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 max-w-lg mx-auto">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $invoice->invoice_number }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Periode: {{ $invoice->period_start?->format('d M Y') }} — {{ $invoice->period_end?->format('d M Y') }}
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium
            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
            {{ match($invoice->status) { 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', default => 'Belum Dibayar' } }}
        </span>
    </div>

    <div class="space-y-3 text-sm border-t pt-4">
        <div class="flex justify-between">
            <span class="text-gray-500">Sewa Pokok</span>
            <span class="font-medium">Rp {{ number_format($invoice->base_amount, 0, ',', '.') }}</span>
        </div>
        @if($invoice->additional_charges)
            @foreach($invoice->additional_charges as $charge)
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $charge['label'] ?? 'Biaya Tambahan' }}</span>
                <span class="font-medium">Rp {{ number_format($charge['amount'] ?? 0, 0, ',', '.') }}</span>
            </div>
            @endforeach
        @endif
        @if($invoice->discount > 0)
        <div class="flex justify-between text-green-600">
            <span>Diskon</span>
            <span>- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="flex justify-between font-semibold text-base border-t pt-3">
            <span>Total</span>
            <span>Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="mt-6 text-sm text-gray-500 space-y-1">
        <div>Jatuh tempo: <strong>{{ $invoice->due_date?->format('d M Y') }}</strong></div>
        @if($invoice->paid_at)
        <div>Dibayar: <strong>{{ $invoice->paid_at->format('d M Y') }}</strong></div>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2">
        <a href="{{ route('print.invoice', $invoice) }}" target="_blank"
           class="flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Tagihan
        </a>
        @if($invoice->status === 'paid')
        <a href="{{ route('print.kwitansi', $invoice) }}" target="_blank"
           class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium py-2.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Cetak Kwitansi
        </a>
        @endif
    </div>

    <a href="{{ route('portal.invoices.index') }}" class="mt-4 block text-center text-blue-600 hover:underline text-sm">
        ← Kembali ke daftar tagihan
    </a>
</div>
@endsection
