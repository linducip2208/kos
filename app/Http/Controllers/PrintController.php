<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Exports\OccupancyExport;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Endpoint print/download PDF dari UI (admin & portal penyewa).
 * Berbeda dari ExportController (API/Sanctum), controller ini pakai session auth
 * sehingga bisa diklik langsung dari halaman Filament/portal.
 */
class PrintController extends Controller
{
    /** Cetak invoice (admin & penyewa pemilik invoice). */
    public function invoice(Invoice $invoice, Request $request)
    {
        $this->authorizeAccess($invoice);
        $invoice->load(['lease.occupant', 'lease.room.property']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        return $request->boolean('download')
            ? $pdf->download("invoice-{$invoice->invoice_number}.pdf")
            : $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    /** Cetak kwitansi (hanya untuk invoice yang sudah lunas). */
    public function kwitansi(Invoice $invoice, Request $request)
    {
        $this->authorizeAccess($invoice);
        abort_unless($invoice->status === 'paid', 404, 'Kwitansi hanya tersedia untuk tagihan yang sudah lunas.');

        $invoice->load(['lease.occupant', 'lease.room.property']);

        $pdf = Pdf::loadView('pdf.kwitansi', compact('invoice'));
        return $request->boolean('download')
            ? $pdf->download("kwitansi-{$invoice->invoice_number}.pdf")
            : $pdf->stream("kwitansi-{$invoice->invoice_number}.pdf");
    }

    /** Laporan tagihan periode (filter status, from, to). */
    public function reportInvoices(Request $request)
    {
        abort_unless(Auth::check(), 403);

        $invoices = Invoice::with(['lease.occupant', 'lease.room.property'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from,   fn ($q) => $q->where('due_date', '>=', $request->from))
            ->when($request->to,     fn ($q) => $q->where('due_date', '<=', $request->to))
            ->orderBy('due_date')
            ->get();

        $pdf = Pdf::loadView('pdf.report-invoices', compact('invoices'));
        return $request->boolean('download')
            ? $pdf->download('laporan-tagihan-' . now()->format('Ymd') . '.pdf')
            : $pdf->stream('laporan-tagihan-' . now()->format('Ymd') . '.pdf');
    }

    /** Excel export tagihan dengan filter status / from / to. */
    public function excelInvoices(Request $request)
    {
        abort_unless(Auth::guard('web')->check(), 403);
        $filename = 'tagihan-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new InvoicesExport($request->status, $request->from, $request->to), $filename);
    }

    /** Excel export occupancy/kontrak dengan filter from / to. */
    public function excelOccupancy(Request $request)
    {
        abort_unless(Auth::guard('web')->check(), 403);
        $filename = 'occupancy-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new OccupancyExport($request->from, $request->to), $filename);
    }

    /** Admin boleh akses semua, penyewa hanya invoice miliknya. */
    private function authorizeAccess(Invoice $invoice): void
    {
        if (Auth::guard('web')->check()) {
            return; // admin
        }
        $occupant = Auth::guard('portal')->user();
        abort_unless($occupant && $invoice->lease?->occupant_id === $occupant->id, 403);
    }
}
