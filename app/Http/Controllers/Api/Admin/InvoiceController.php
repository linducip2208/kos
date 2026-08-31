<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize($request, 'invoice.view');
        $propertyIds = $request->user()->scopedPropertyIds();
        $invoices = Invoice::with(['lease.occupant:id,name', 'lease.room:id,room_number'])
            ->when($propertyIds !== null, fn ($query) => $query->whereHas('lease.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->from, fn ($q) => $q->where('due_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('due_date', '<=', $request->to))
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json($invoices);
    }

    public function store(Request $request)
    {
        $this->authorize($request, 'invoice.manage');
        $data = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'due_date' => 'required|date',
            'base_amount' => 'required|numeric|min:0',
            'additional_charges' => 'nullable|array',
            'additional_charges.*.label' => 'required|string|max:120',
            'additional_charges.*.amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $this->assertLeaseScope($request, (int) $data['lease_id']);

        $data['invoice_number'] = $this->generateNumber();
        $data['total'] = $this->calculateTotal($data);
        $data['status'] = 'draft';

        $invoice = Invoice::create($data);

        return response()->json($invoice->load('lease.occupant'), 201);
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice(request(), $invoice, 'invoice.view');

        return response()->json($invoice->load(['lease.occupant', 'lease.room.property']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice, 'invoice.manage');
        $data = $request->validate([
            'period_start' => 'sometimes|date',
            'period_end' => 'sometimes|date',
            'due_date' => 'sometimes|date',
            'base_amount' => 'sometimes|numeric|min:0',
            'additional_charges' => 'nullable|array',
            'additional_charges.*.label' => 'required|string|max:120',
            'additional_charges.*.amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $data['total'] = $this->calculateTotal(array_merge($invoice->only(['base_amount', 'discount', 'additional_charges']), $data));
        $invoice->update($data);

        return response()->json($invoice);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice, 'payment.record');
        $data = $request->validate([
            'payment_method' => 'nullable|string|max:50',
            'payment_ref' => 'nullable|string|max:100',
            'paid_at' => 'nullable|date',
        ]);
        $payment = app(PaymentService::class)->markPaidWithRecord($invoice, $data['payment_method'] ?? 'cash', $data['payment_ref'] ?? null);

        return response()->json(['message' => 'Pembayaran dicatat dan diverifikasi.', 'payment' => $payment, 'invoice' => $invoice->fresh()]);
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeInvoice(request(), $invoice, 'invoice.manage');
        $invoice->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Tagihan dibatalkan.']);
    }

    private function generateNumber(): string
    {
        $prefix = setting('invoice_prefix', 'INV');
        $year = date('Y');
        $month = date('m');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $count);
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }

    private function authorizeInvoice(Request $request, Invoice $invoice, string $permission): void
    {
        $this->authorize($request, $permission);
        $propertyIds = $request->user()->scopedPropertyIds();
        abort_unless($propertyIds === null || in_array($invoice->loadMissing('lease.room')->lease?->room?->property_id, $propertyIds, true), 403);
    }

    private function assertLeaseScope(Request $request, int $leaseId): void
    {
        $propertyIds = $request->user()->scopedPropertyIds();
        if ($propertyIds === null) {
            return;
        }

        abort_unless(Lease::whereKey($leaseId)->whereHas('room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0]))->exists(), 403);
    }

    private function calculateTotal(array $data): float
    {
        $base = (float) ($data['base_amount'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $extras = collect($data['additional_charges'] ?? [])->sum(fn ($charge) => (float) ($charge['amount'] ?? 0));
        abort_unless($discount <= $base + $extras + 0.009, 422, 'Diskon melebihi nilai invoice.');

        return round(max(0, $base + $extras - $discount), 2);
    }
}
