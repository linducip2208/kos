<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\InvoicesExport;
use App\Exports\OccupancyExport;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:invoices,occupancy',
            'format' => 'required|in:xlsx,csv,pdf',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $type   = $request->type;
        $format = $request->format;

        return match (true) {
            $type === 'invoices'  && $format === 'pdf'  => $this->invoicePdf($request),
            $type === 'invoices'                        => $this->invoicesExcel($request, $format),
            $type === 'occupancy' && $format === 'pdf'  => response()->json(['message' => 'PDF occupancy belum didukung, gunakan format xlsx.'], 422),
            $type === 'occupancy'                       => $this->occupancyExcel($request, $format),
            default => response()->json(['message' => 'Kombinasi type/format tidak dikenali.'], 422),
        };
    }

    public function invoicePdfSingle(Invoice $invoice)
    {
        $invoice->load(['lease.occupant', 'lease.room.property']);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    private function invoicesExcel(Request $request, string $format): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $export   = new InvoicesExport($request->status, $request->from, $request->to);
        $filename = "invoices-" . now()->format('Ymd') . ".{$format}";

        return Excel::download($export, $filename);
    }

    private function occupancyExcel(Request $request, string $format): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $export   = new OccupancyExport($request->from, $request->to);
        $filename = "occupancy-" . now()->format('Ymd') . ".{$format}";

        return Excel::download($export, $filename);
    }

    private function invoicePdf(Request $request): \Illuminate\Http\Response
    {
        $invoices = Invoice::with(['lease.occupant', 'lease.room.property'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from,   fn ($q) => $q->where('due_date', '>=', $request->from))
            ->when($request->to,     fn ($q) => $q->where('due_date', '<=', $request->to))
            ->orderBy('due_date')
            ->get();

        $pdf = Pdf::loadView('pdf.report-invoices', compact('invoices'));
        return $pdf->download("laporan-tagihan-" . now()->format('Ymd') . ".pdf");
    }
}
