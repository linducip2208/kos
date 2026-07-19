<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $occupant    = $request->user();
        $activeLease = $occupant->leases()->where('status', 'active')->with('room.property')->latest()->first();

        $unpaidCount = $occupant->invoices()->whereIn('invoices.status', ['sent', 'overdue'])->count();
        $unpaidTotal = $occupant->invoices()->whereIn('invoices.status', ['sent', 'overdue'])->sum('invoices.total');
        $openMaintenance = $occupant->maintenanceRequests()->whereNotIn('status', ['resolved', 'cancelled'])->count();

        return response()->json([
            'lease'            => $activeLease ? [
                'id'          => $activeLease->id,
                'room_number' => $activeLease->room?->room_number,
                'property'    => $activeLease->room?->property?->name,
                'start_date'  => $activeLease->start_date,
                'end_date'    => $activeLease->end_date,
                'status'      => $activeLease->status,
            ] : null,
            'unpaid_invoices'  => $unpaidCount,
            'unpaid_total'     => $unpaidTotal,
            'open_maintenance' => $openMaintenance,
        ]);
    }
}
