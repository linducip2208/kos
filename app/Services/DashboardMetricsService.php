<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
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
            'pending_bookings' => BookingRequest::query()->where(function ($query) {
                $query->whereIn('status', ['pending', 'inquiry', 'qualified', 'room_offered'])
                    ->orWhereIn('stage', ['new_lead', 'contacted', 'viewing_scheduled', 'interested', 'reserved', 'deposit_pending']);
            })->when($propertyIds !== null, fn ($query) => $query->whereIn('property_id', $propertyIds ?: [0]))->count(),
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

    public function revenueTrend(User $user, Carbon $from, Carbon $to, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);
        $invoices = Invoice::query()->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
            ->when($propertyIds !== null, fn ($query) => $query->whereHas('lease.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])));

        $rows = collect();
        for ($date = $from->copy()->startOfMonth(); $date->lte($to); $date->addMonth()) {
            $month = $date->copy();
            $monthInvoices = (clone $invoices)->whereYear('due_date', $month->year)->whereMonth('due_date', $month->month);
            $rows->push([
                'label' => $month->translatedFormat('M Y'),
                'billed' => (float) $monthInvoices->sum('total'),
                'collected' => (float) $monthInvoices->withSum(['verifiedPayments as collected_sum' => fn ($query) => $query->whereYear('paid_at', $month->year)->whereMonth('paid_at', $month->month)], 'amount')->get()->sum('collected_sum'),
            ]);
        }

        return $rows->all();
    }

    public function receivableAging(User $user, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);
        $invoices = Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])
            ->when($propertyIds !== null, fn ($query) => $query->whereHas('lease.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))->get();
        $buckets = ['Berjalan' => 0, '1–30 hari' => 0, '31–60 hari' => 0, '61–90 hari' => 0, '>90 hari' => 0];
        foreach ($invoices as $invoice) {
            $days = $invoice->due_date?->isPast() ? $invoice->due_date->diffInDays(today()) : 0;
            $bucket = $days === 0 ? 'Berjalan' : ($days <= 30 ? '1–30 hari' : ($days <= 60 ? '31–60 hari' : ($days <= 90 ? '61–90 hari' : '>90 hari')));
            $buckets[$bucket] += (float) $invoice->balance_due;
        }

        return $buckets;
    }

    public function bookingFunnel(User $user, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);
        $query = BookingRequest::query()->when($propertyIds !== null, fn ($q) => $q->whereIn('property_id', $propertyIds ?: [0]));

        return collect([
            'Lead' => ['new_lead', 'pending'],
            'Dihubungi' => ['contacted', 'inquiry'],
            'Ditawarkan' => ['viewing_scheduled', 'interested', 'qualified', 'room_offered'],
            'Reserved' => ['reserved', 'deposit_pending'],
            'Check-in' => ['move_in', 'converted', 'approved'],
        ])->mapWithKeys(fn (array $stages, string $label) => [$label => (clone $query)->where(function ($q) use ($stages) {
            $q->whereIn('stage', $stages)->orWhereIn('status', $stages);
        })->count()])->all();
    }

    public function maintenanceOverview(User $user, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);
        $query = MaintenanceRequest::query()->when($propertyIds !== null, fn ($q) => $q->whereHas('room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])));

        return collect(['Open' => ['open'], 'Ditugaskan' => ['assigned'], 'Dikerjakan' => ['in_progress'], 'Selesai' => ['resolved', 'completed', 'closed']])
            ->mapWithKeys(fn (array $statuses, string $label) => [$label => (clone $query)->whereIn('status', $statuses)->count()])->all();
    }

    public function propertyComparison(User $user): array
    {
        $properties = $user->scopedPropertyIds();
        $query = Property::query()->when($properties !== null, fn ($q) => $q->whereIn('id', $properties ?: [0]));

        return $query->withCount(['rooms', 'rooms as occupied_rooms_count' => fn ($room) => $room->where('status', 'occupied')])->get()->map(fn ($property) => [
            'label' => $property->name,
            'occupancy' => $property->rooms_count > 0 ? round($property->occupied_rooms_count / $property->rooms_count * 100, 1) : 0,
            'rooms' => $property->rooms_count,
        ])->all();
    }

    private function propertyIds(User $user, ?int $propertyId): ?array
    {
        $ids = $user->scopedPropertyIds();
        if ($propertyId !== null) {
            $ids = $ids === null ? [$propertyId] : array_values(array_intersect($ids, [$propertyId]));
        }

        return $ids;
    }
}
