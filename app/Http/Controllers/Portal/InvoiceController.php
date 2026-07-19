<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $occupant = Auth::guard('portal')->user();
        $invoices = $occupant->invoices()->orderBy('invoices.due_date', 'desc')->paginate(10);
        return view('portal.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $occupant = Auth::guard('portal')->user();
        abort_if($invoice->lease?->occupant_id !== $occupant->id, 403);
        return view('portal.invoices.show', compact('invoice'));
    }
}
