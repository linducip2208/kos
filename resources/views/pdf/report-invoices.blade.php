<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
  h2 { color: #6366f1; margin-bottom: 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 12px; }
  th { background: #6366f1; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
  td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .total { font-weight: bold; }
  .footer { margin-top: 20px; text-align: center; color: #94a3b8; font-size: 9px; }
  .paid { color: #16a34a; } .overdue { color: #dc2626; } .sent { color: #2563eb; }
</style>
</head>
<body>
<h2>Laporan Tagihan</h2>
<p style="color:#64748b;">Dicetak: {{ now()->format('d M Y H:i') }} &bull; Total: {{ $invoices->count() }} tagihan</p>

<table>
  <thead>
    <tr>
      <th>No. Invoice</th><th>Penyewa</th><th>Kamar</th><th>Jatuh Tempo</th><th style="text-align:right">Total</th><th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($invoices as $inv)
    <tr>
      <td>{{ $inv->invoice_number }}</td>
      <td>{{ $inv->lease->occupant->name ?? '-' }}</td>
      <td>{{ $inv->lease->room->room_number ?? '-' }}</td>
      <td>{{ $inv->due_date }}</td>
      <td style="text-align:right">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
      <td class="{{ $inv->status }}">{{ strtoupper($inv->status) }}</td>
    </tr>
    @endforeach
    <tr>
      <td colspan="4" class="total">TOTAL</td>
      <td style="text-align:right" class="total">Rp {{ number_format($invoices->sum('total'), 0, ',', '.') }}</td>
      <td></td>
    </tr>
  </tbody>
</table>

<div class="footer">{{ setting('app_name', 'Kos Manager') }}</div>
</body>
</html>
