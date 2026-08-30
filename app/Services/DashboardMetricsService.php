<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;

final class DashboardMetricsService
{
    public function for(User $user, ?Carbon $from = null, ?Carbon $to = null, ?int $propertyId = null): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now()->endOfDay();
        $propertyIds = $user->scopedPropertyIds();
        if ($propertyId !== null) {
            $propertyIds = $propertyIds === null ? [$propertyId] : array_values(array_intersect($propertyIds, [$propertyId]));
        }
        $propertyScope = fn ($query, string $column = 'property_id') => $propertyIds === null ? $query : $query->whereIn($column, $propertyIds ?: [0]);

        $rooms = Room::query()->where('is_active', true);
        $propertyScope($rooms);
        $totalRooms = (clone $rooms)->count();
        $occupiedRooms = (clone $rooms)->where('status', 'occupied')->count();
        $leases = Lease::query()->whereIn('status', ['active', 'expiring_soon'])->whereHas('room', fn ($q) => $propertyScope($q));
        $maintenance = MaintenanceRequest::query()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->whereHas('room', fn ($q) => $propertyScope($q));

        $metrics = [
            'total_rooms' => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms' => (clone $rooms)->where('status', 'available')->count(),
            'maintenance_rooms' => (clone $rooms)->where('status', 'maintenance')->count(),
            'occupancy_rate' => $totalRooms > 0 ? round($occupiedRooms / $totalRooms * 100, 1) : 0,
            'active_tenants' => (clone $leases)->distinct('occupant_id')->count('occupant_id'),
            'expiring_leases' => (clone $leases)->whereBetween('end_date', [today(), today()->addDays(30)])->count(),
            'open_maintenance' => $maintenance->count(),
            'pending_bookings' => BookingRequest::query()->whereIn('status', ['pending', 'inquiry', 'qualified', 'room_offered'])->count(),
        ];

        $canFinance = $user->isOwner() || $user->isSuperAdmin() || in_array($user->role, ['finance', 'cashier', 'auditor'], true);
        if ($canFinance) {
            $invoices = Invoice::query()->whereHas('lease.room', fn ($q) => $propertyScope($q));
            $periodInvoices = (clone $invoices)->whereBetween('due_date', [$from->toDateString(), $to->toDateString()]);
            $metrics += [
                'billed' => (float) $periodInvoices->sum('total'),
                'collected' => (float) $periodInvoices->withSum(['verifiedPayments as collected_sum' => fn ($q) => $q->whereBetween('paid_at', [$from, $to])], 'amount')->get()->sum('collected_sum'),
                'receivable' => (float) (clone $invoices)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('total'),
                'overdue_invoices' => (clone $invoices)->whereIn('status', ['sent', 'partial', 'overdue'])->where('due_date', '<', today())->count(),
                'expenses' => (float) $propertyScope(Expense::query()->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]))->sum('amount'),
            ];
        }

        return $metrics;
    }
}
