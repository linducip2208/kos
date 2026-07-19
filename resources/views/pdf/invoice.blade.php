<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 0; padding: 20px; }
  .header { display: flex; justify-content: space-between; margin-bottom: 24px; border-bottom: 2px solid #6366f1; padding-bottom: 12px; }
  .app-name { font-size: 20px; font-weight: bold; color: #6366f1; }
  .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 11px; }
  .badge-paid { background: #dcfce7; color: #166534; }
  .badge-overdue { background: #fee2e2; color: #991b1b; }
  .badge-sent { background: #dbeafe; color: #1e40af; }
  .badge-draft { background: #f1f5f9; color: #475569; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  th { background: #6366f1; color: white; padding: 8px; text-align: left; }
  td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
  .total-row td { font-weight: bold; background: #f8fafc; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
  .info-box { background: #f8fafc; padding: 12px; border-radius: 6px; }
  .info-box label { font-size: 10px; color: #64748b; text-transform: uppercase; }
  .info-box p { margin: 2px 0 0; font-weight: bold; }
  .footer { margin-top: 32px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 12px; }
</style>
</head>
<body>

<div class="header">
  <div>
    <div class="app-name">{{ setting('app_name', 'Kos Manager') }}</div>
    <div style="color:#64748b;margin-top:4px;">{{ setting('contact_address', '') }}</div>
  </div>
  <div style="text-align:right;">
    <div style="font-size:18px;font-weight:bold;">TAGIHAN</div>
    <div style="color:#64748b;">{{ $invoice->invoice_number }}</div>
    <div style="margin-top:6px;">
      <span class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
    </div>
  </div>
</div>

<div class="info-grid">
  <div class="info-box">
    <label>Tagihan Untuk</label>
    <p>{{ $invoice->lease->occupant->name }}</p>
    <div style="color:#64748b;font-size:11px;">{{ $invoice->lease->occupant->phone }}</div>
  </div>
  <div class="info-box">
    <label>Kamar</label>
    <p>{{ $invoice->lease->room->property->name }} — {{ $invoice->lease->room->room_number }}</p>
    <div style="color:#64748b;font-size:11px;">Kontrak: {{ $invoice->lease->lease_number }}</div>
  </div>
  <div class="info-box">
    <label>Periode</label>
    <p>{{ \Carbon\Carbon::parse($invoice->period_start)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($invoice->period_end)->format('d M Y') }}</p>
  </div>
  <div class="info-box">
    <label>Jatuh Tempo</label>
    <p>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>Keterangan</th>
      <th style="text-align:right;">Jumlah</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Sewa Kamar — Periode {{ \Carbon\Carbon::parse($invoice->period_start)->format('M Y') }}</td>
      <td style="text-align:right;">Rp {{ number_format($invoice->base_amount, 0, ',', '.') }}</td>
    </tr>
    @foreach($invoice->additional_charges ?? [] as $charge)
    <tr>
      <td>{{ $charge['label'] }}</td>
      <td style="text-align:right;">Rp {{ number_format($charge['amount'], 0, ',', '.') }}</td>
    </tr>
    @endforeach
    @if($invoice->discount > 0)
    <tr>
      <td>Diskon</td>
      <td style="text-align:right;color:#16a34a;">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
      <td>TOTAL</td>
      <td style="text-align:right;">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
    </tr>
  </tbody>
</table>

@if($invoice->paid_at)
<p style="color:#16a34a;margin-top:12px;">✓ Dibayar pada {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }} via {{ $invoice->payment_method ?? '-' }}</p>
@endif

@if($invoice->notes)
<p style="background:#fffbeb;padding:8px;border-radius:4px;margin-top:12px;"><strong>Catatan:</strong> {{ $invoice->notes }}</p>
@endif

<div class="footer">
  {{ setting('app_name', 'Kos Manager') }} &bull; {{ setting('contact_email', '') }} &bull; {{ setting('contact_phone', '') }}<br>
  Dicetak pada {{ now()->format('d M Y H:i') }}
</div>

</body>
</html>
