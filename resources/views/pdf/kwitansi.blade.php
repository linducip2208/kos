<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 24px; }
    .receipt { border: 2px solid #16a34a; border-radius: 8px; padding: 24px; max-width: 700px; margin: 0 auto; position: relative; }
    .stamp { position: absolute; top: 18px; right: 18px; transform: rotate(-12deg); border: 3px solid #16a34a; color: #16a34a; font-weight: bold; font-size: 18px; padding: 6px 14px; border-radius: 6px; opacity: .85; letter-spacing: 1px; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 1px dashed #cbd5e1; margin-bottom: 16px; }
    .app-name { font-size: 18px; font-weight: bold; color: #15803d; }
    .doc-title { font-size: 22px; font-weight: 800; color: #15803d; letter-spacing: 1px; }
    .doc-num { color: #64748b; font-size: 11px; margin-top: 2px; }
    table.info { width: 100%; margin-bottom: 16px; }
    table.info td { padding: 4px 0; font-size: 12px; vertical-align: top; }
    table.info td.label { color: #64748b; width: 35%; }
    .amount-box { background: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 18px 20px; margin: 18px 0; text-align: center; }
    .amount-label { font-size: 11px; text-transform: uppercase; color: #15803d; letter-spacing: 1px; }
    .amount-value { font-size: 28px; font-weight: 800; color: #166534; margin: 4px 0; }
    .amount-words { font-size: 11px; color: #475569; font-style: italic; }
    .footer-grid { display: table; width: 100%; margin-top: 28px; }
    .footer-cell { display: table-cell; width: 50%; padding: 0 12px; }
    .signature-line { margin-top: 60px; padding-top: 4px; border-top: 1px solid #475569; text-align: center; font-size: 11px; color: #475569; }
    .meta { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 10px; padding-top: 12px; border-top: 1px dashed #cbd5e1; }
</style>
</head>
<body>

<div class="receipt">
    @if($invoice->status === 'paid')
        <div class="stamp">LUNAS</div>
    @endif

    <div class="header">
        <div>
            <div class="app-name">{{ setting('app_name', 'Kos Manager') }}</div>
            <div style="color:#64748b;font-size:11px;margin-top:2px;">{{ setting('contact_address', '') }}</div>
            @if(setting('contact_phone')) <div style="color:#64748b;font-size:11px;">Telp: {{ setting('contact_phone') }}</div> @endif
        </div>
        <div style="text-align:right;">
            <div class="doc-title">KWITANSI</div>
            <div class="doc-num">No. {{ str_replace('INV-', 'KW-', $invoice->invoice_number) }}</div>
            <div class="doc-num">Ref. Invoice: {{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Telah diterima dari</td>
            <td><strong>{{ $invoice->lease->occupant->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Untuk pembayaran</td>
            <td>Sewa kamar <strong>{{ $invoice->lease->room->property->name ?? '-' }}</strong> &mdash; No. {{ $invoice->lease->room->room_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Periode</td>
            <td>{{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Metode pembayaran</td>
            <td>{{ ucfirst($invoice->payment_method ?? '-') }}@if($invoice->payment_ref) &mdash; Ref: {{ $invoice->payment_ref }}@endif</td>
        </tr>
        <tr>
            <td class="label">Tanggal pembayaran</td>
            <td>{{ $invoice->paid_at ? \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') : '-' }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="amount-label">Jumlah Diterima</div>
        <div class="amount-value">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
        <div class="amount-words">Terbilang: {{ ucwords(\App\Helpers\Terbilang::make($invoice->total)) }} rupiah</div>
    </div>

    <div class="footer-grid">
        <div class="footer-cell">
            <div style="font-size:11px;color:#64748b;">Dibayar oleh</div>
            <div class="signature-line">{{ $invoice->lease->occupant->name ?? '-' }}</div>
        </div>
        <div class="footer-cell">
            <div style="font-size:11px;color:#64748b;text-align:right;">{{ setting('contact_address') ? explode(',', setting('contact_address'))[0] : '' }}, {{ now()->format('d M Y') }}</div>
            <div class="signature-line">Pengelola</div>
        </div>
    </div>

    <div class="meta">
        Kwitansi ini sah sebagai bukti pembayaran. Dicetak otomatis oleh {{ setting('app_name', 'Kos Manager') }} pada {{ now()->format('d M Y H:i') }}.
    </div>
</div>

</body>
</html>
