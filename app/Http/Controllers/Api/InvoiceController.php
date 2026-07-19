<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = $request->user()->invoices()
            ->orderBy('invoices.due_date', 'desc')
            ->paginate(15);

        return response()->json([
            'data'  => $invoices->map(fn ($inv) => $this->invoiceData($inv)),
            'total' => $invoices->total(),
            'page'  => $invoices->currentPage(),
            'pages' => $invoices->lastPage(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $invoice = $request->user()->invoices()->findOrFail($id);
        return response()->json($this->invoiceData($invoice, true));
    }

    private function invoiceData(Invoice $inv, bool $withItems = false): array
    {
        $data = [
            'id'             => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'status'         => $inv->status,
            'total'          => $inv->total,
            'due_date'       => $inv->due_date,
            'paid_at'        => $inv->paid_at,
            'period_start'   => $inv->period_start,
            'period_end'     => $inv->period_end,
        ];
        if ($withItems && isset($inv->items)) {
            $data['items'] = $inv->items->map(fn ($i) => [
                'description' => $i->description,
                'amount'      => $i->amount,
            ]);
        }
        return $data;
    }
}
