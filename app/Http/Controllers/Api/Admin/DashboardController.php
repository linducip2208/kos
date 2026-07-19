<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalRooms     = Room::count();
        $occupiedRooms  = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();

        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $overdueTotal = Invoice::where('status', 'overdue')->sum('total');
        $overdueCount = Invoice::where('status', 'overdue')->count();

        return response()->json([
            'rooms' => [
                'total'     => $totalRooms,
                'occupied'  => $occupiedRooms,
                'available' => $availableRooms,
                'occupancy_rate' => $totalRooms ? round($occupiedRooms / $totalRooms * 100, 1) : 0,
            ],
            'revenue' => [
                'this_month' => $revenueThisMonth,
            ],
            'overdue' => [
                'count' => $overdueCount,
                'total' => $overdueTotal,
            ],
        ]);
    }

    public function upcoming()
    {
        $expiringLeases = Lease::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
            ->get(['id', 'lease_number', 'end_date', 'occupant_id', 'room_id']);

        $dueSoonInvoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<=', now()->addDays(7))
            ->with(['lease.occupant:id,name'])
            ->get(['id', 'invoice_number', 'due_date', 'total', 'status', 'lease_id']);

        return response()->json([
            'expiring_leases'  => $expiringLeases,
            'due_soon_invoices' => $dueSoonInvoices,
        ]);
    }

    public function recentActivity()
    {
        $recentInvoices = Invoice::with(['lease.occupant:id,name'])
            ->latest()
            ->limit(10)
            ->get(['id', 'invoice_number', 'total', 'status', 'created_at', 'lease_id']);

        $recentLeases = Lease::with(['occupant:id,name', 'room:id,room_number'])
            ->latest()
            ->limit(5)
            ->get(['id', 'lease_number', 'status', 'created_at', 'occupant_id', 'room_id']);

        return response()->json([
            'recent_invoices' => $recentInvoices,
            'recent_leases'   => $recentLeases,
        ]);
    }
}
