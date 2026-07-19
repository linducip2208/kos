@extends('portal.layouts.app')
@section('title', 'Tagihan')

@section('content')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="p-5 border-b">
        <h2 class="font-semibold text-gray-700">Riwayat Tagihan</h2>
    </div>

    @forelse($invoices as $invoice)
    <div class="flex items-center justify-between p-4 border-b last:border-0 hover:bg-gray-50">
        <div>
            <div class="font-medium text-sm">{{ $invoice->invoice_number }}</div>
            <div class="text-xs text-gray-500 mt-0.5">
                Periode: {{ $invoice->period_start?->format('M Y') }} •
                Jatuh tempo: {{ $invoice->due_date?->format('d M Y') }}
            </div>
        </div>
        <div class="text-right">
            <div class="font-semibold text-sm">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
            <span class="text-xs px-1.5 py-0.5 rounded mt-1 inline-block
                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                {{ match($invoice->status) { 'paid' => 'Lunas', 'overdue' => 'Jatuh Tempo', default => 'Belum Dibayar' } }}
            </span>
        </div>
    </div>
    @empty
    <div class="p-8 text-center text-gray-400">
        <div class="text-4xl mb-2">📭</div>
        Belum ada tagihan.
    </div>
    @endforelse

    <div class="p-4">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
