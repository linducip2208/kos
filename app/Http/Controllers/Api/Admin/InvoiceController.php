<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['lease.occupant:id,name', 'lease.room:id,room_number'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q) => $q->where('due_date', '>=', $request->from))
            ->when($request->to,   fn ($q) => $q->where('due_date', '<=', $request->to))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'lease_id'           => 'required|exists:leases,id',
            'period_start'       => 'required|date',
            'period_end'         => 'required|date|after:period_start',
            'due_date'           => 'required|date',
            'base_amount'        => 'required|numeric|min:0',
            'additional_charges' => 'nullable|array',
            'discount'           => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $data['invoice_number'] = $this->generateNumber();
        $data['total']          = ($data['base_amount'] - ($data['discount'] ?? 0))
            + collect($data['additional_charges'] ?? [])->sum('amount');
        $data['status']         = 'draft';

        $invoice = Invoice::create($data);
        return response()->json($invoice->load('lease.occupant'), 201);
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice->load(['lease.occupant', 'lease.room.property']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'period_start'       => 'sometimes|date',
            'period_end'         => 'sometimes|date',
            'due_date'           => 'sometimes|date',
            'base_amount'        => 'sometimes|numeric|min:0',
            'additional_charges' => 'nullable|array',
            'discount'           => 'nullable|numeric|min:0',
            'status'             => 'in:draft,sent,paid,overdue,cancelled',
            'notes'              => 'nullable|string',
        ]);

        $invoice->update($data);
        return response()->json($invoice);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'payment_ref'    => 'nullable|string|max:100',
            'paid_at'        => 'nullable|date',
        ]);

        $invoice->update([
            'status'         => 'paid',
            'paid_at'        => $request->paid_at ?? now(),
            'payment_method' => $request->payment_method,
            'payment_ref'    => $request->payment_ref,
        ]);

        return response()->json(['message' => 'Tagihan ditandai lunas.', 'invoice' => $invoice]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Tagihan dibatalkan.']);
    }

    private function generateNumber(): string
    {
        $prefix = setting('invoice_prefix', 'INV');
        $year   = date('Y');
        $month  = date('m');
        $count  = Invoice::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $count);
    }
}
