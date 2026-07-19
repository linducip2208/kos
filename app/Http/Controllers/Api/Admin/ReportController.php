<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function occupancy(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $totalRooms    = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();

        $newLeases = Lease::whereBetween('start_date', [$from, $to])->count();
        $endedLeases = Lease::whereBetween('end_date', [$from, $to])
            ->whereIn('status', ['expired', 'terminated'])->count();

        return response()->json([
            'period'        => ['from' => $from, 'to' => $to],
            'total_rooms'   => $totalRooms,
            'occupied'      => $occupiedRooms,
            'available'     => $totalRooms - $occupiedRooms,
            'occupancy_rate'=> $totalRooms ? round($occupiedRooms / $totalRooms * 100, 1) : 0,
            'new_leases'    => $newLeases,
            'ended_leases'  => $endedLeases,
        ]);
    }

    public function revenue(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $paid = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        $overdue = Invoice::where('status', 'overdue')
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        $byMonth = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("strftime('%Y-%m', paid_at) as month, SUM(total) as total, COUNT(*) as count")
            ->groupByRaw("strftime('%Y-%m', paid_at)")
            ->orderByRaw("strftime('%Y-%m', paid_at)")
            ->get();

        return response()->json([
            'period'           => ['from' => $from, 'to' => $to],
            'paid_count'       => $paid->count,
            'paid_total'       => $paid->total ?? 0,
            'overdue_count'    => $overdue->count,
            'overdue_total'    => $overdue->total ?? 0,
            'by_month'         => $byMonth,
        ]);
    }

    public function overdue()
    {
        $invoices = Invoice::where('status', 'overdue')
            ->with(['lease.occupant:id,name,phone', 'lease.room:id,room_number,property_id', 'lease.room.property:id,name'])
            ->orderBy('due_date')
            ->get(['id', 'invoice_number', 'due_date', 'total', 'lease_id']);

        return response()->json([
            'count'    => $invoices->count(),
            'total'    => $invoices->sum('total'),
            'invoices' => $invoices,
        ]);
    }

    public function turnover(Request $request)
    {
        $from = $request->from ?? now()->subMonths(6)->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $terminated = Lease::whereBetween('terminated_at', [$from, $to])
            ->with(['occupant:id,name', 'room:id,room_number'])
            ->get(['id', 'lease_number', 'terminated_at', 'termination_reason', 'occupant_id', 'room_id']);

        $expired = Lease::where('status', 'expired')
            ->whereBetween('end_date', [$from, $to])
            ->with(['occupant:id,name', 'room:id,room_number'])
            ->get(['id', 'lease_number', 'end_date', 'occupant_id', 'room_id']);

        return response()->json([
            'period'     => ['from' => $from, 'to' => $to],
            'terminated' => $terminated,
            'expired'    => $expired,
        ]);
    }
}
