<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $occupant   = Auth::guard('portal')->user();
        $activeLease = $occupant->leases()->where('status', 'active')->with('room.property')->latest()->first();

        $unpaidInvoices = $occupant->invoices()
            ->whereIn('invoices.status', ['sent', 'overdue'])
            ->orderBy('invoices.due_date')->take(3)->get();

        $recentInvoices = $occupant->invoices()
            ->latest('invoices.created_at')->take(5)->get();

        $openMaintenance = $occupant->maintenanceRequests()
            ->whereNotIn('status', ['resolved', 'cancelled'])->count();

        return view('portal.dashboard.index', compact(
            'occupant', 'activeLease', 'unpaidInvoices', 'recentInvoices', 'openMaintenance'
        ));
    }
}
