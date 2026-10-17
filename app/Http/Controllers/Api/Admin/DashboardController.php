<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $this->authorize($request, 'dashboard.view');
        $propertyIds = $request->user()->scopedPropertyIds();

        $roomQuery = Room::query()
            ->when($propertyIds !== null, fn ($q) => $q->whereIn('property_id', $propertyIds ?: [0]));
        $leaseInScope = fn ($q) => $q->whereHas('room', fn ($r) => $propertyIds === null
            ? $r
            : $r->whereIn('property_id', $propertyIds ?: [0]));
        $invoiceInScope = fn ($q) => $q->whereHas('lease.room', fn ($r) => $propertyIds === null
            ? $r
            : $r->whereIn('property_id', $propertyIds ?: [0]));

        $totalRooms     = (clone $roomQuery)->count();
        $occupiedRooms  = (clone $roomQuery)->where('status', 'occupied')->count();
        $availableRooms = (clone $roomQuery)->where('status', 'available')->count();

        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->where($invoiceInScope)
            ->sum('total');

        $overdueTotal = Invoice::where('status', 'overdue')->where($invoiceInScope)->sum('total');
        $overdueCount = Invoice::where('status', 'overdue')->where($invoiceInScope)->count();

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

    public function upcoming(Request $request)
    {
        $this->authorize($request, 'dashboard.view');
        $propertyIds = $request->user()->scopedPropertyIds();

        $expiringLeases = Lease::whereIn('status', ['active', 'expiring_soon'])
            ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->when($propertyIds !== null, fn ($q) => $q->whereHas('room',
                fn ($r) => $r->whereIn('property_id', $propertyIds ?: [0])))
            ->with(['occupant:id,name,phone', 'room:id,room_number,property_id', 'room.property:id,name'])
            ->get(['id', 'lease_number', 'end_date', 'occupant_id', 'room_id']);

        $dueSoonInvoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<=', now()->addDays(7))
            ->when($propertyIds !== null, fn ($q) => $q->whereHas('lease.room',
                fn ($r) => $r->whereIn('property_id', $propertyIds ?: [0])))
            ->with(['lease.occupant:id,name'])
            ->get(['id', 'invoice_number', 'due_date', 'total', 'status', 'lease_id']);

        return response()->json([
            'expiring_leases'  => $expiringLeases,
            'due_soon_invoices' => $dueSoonInvoices,
        ]);
    }

    public function recentActivity(Request $request)
    {
        $this->authorize($request, 'dashboard.view');
        $propertyIds = $request->user()->scopedPropertyIds();

        $recentInvoices = Invoice::with(['lease.occupant:id,name'])
            ->when($propertyIds !== null, fn ($q) => $q->whereHas('lease.room',
                fn ($r) => $r->whereIn('property_id', $propertyIds ?: [0])))
            ->latest()
            ->limit(10)
            ->get(['id', 'invoice_number', 'total', 'status', 'created_at', 'lease_id']);

        $recentLeases = Lease::with(['occupant:id,name', 'room:id,room_number'])
            ->when($propertyIds !== null, fn ($q) => $q->whereHas('room',
                fn ($r) => $r->whereIn('property_id', $propertyIds ?: [0])))
            ->latest()
            ->limit(5)
            ->get(['id', 'lease_number', 'status', 'created_at', 'occupant_id', 'room_id']);

        return response()->json([
            'recent_invoices' => $recentInvoices,
            'recent_leases'   => $recentLeases,
        ]);
    }

    private function authorize(Request $request, string $permission): void
    {
        abort_unless(Gate::forUser($request->user())->allows($permission), 403);
    }
}
