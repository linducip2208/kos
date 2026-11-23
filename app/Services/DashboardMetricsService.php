<?php

namespace App\Services;

use App\Models\BookingRequest;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class DashboardMetricsService
{
    public function for(User $user, ?Carbon $from = null, ?Carbon $to = null, ?int $propertyId = null): array
    {
        $from ??= now()->startOfMonth();
        $to ??= now()->endOfDay();
        $cacheKey = sprintf(
            'dashboard-metrics:%d:%s:%s:%s',
            $user->id, $from->format('Y-m-d'), $to->format('Y-m-d'), $propertyId ?? 'all'
        );

        return Cache::remember($cacheKey, 300, function () use ($user, $from, $to, $propertyId) {
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
            // Kas diterima: 1 query agregat ke invoice_payments (hindari load semua invoice ke memori).
            $collectedQuery = InvoicePayment::query()
                ->where('type', 'payment')->where('status', 'verified')
                ->whereBetween('paid_at', [$from, $to])
                ->whereHas('invoice', function ($q) use ($from, $to, $propertyScope) {
                    $q->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                        ->whereHas('lease.room', fn ($room) => $propertyScope($room));
                });
            $metrics += [
                'billed' => (float) $periodInvoices->sum('total'),
                'collected' => (float) $collectedQuery->sum('amount'),
                'receivable' => (float) (clone $invoices)->whereIn('status', ['sent', 'partial', 'overdue'])->sum('total'),
                'overdue_invoices' => (clone $invoices)->whereIn('status', ['sent', 'partial', 'overdue'])->where('due_date', '<', today())->count(),
                'expenses' => (float) $propertyScope(Expense::query()->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]))->sum('amount'),
            ];
        }

        return $metrics;
        });
    }

    public function revenueTrend(User $user, Carbon $from, Carbon $to, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);

        return Cache::remember(
            sprintf('revenue-trend:%d:%s:%s:%s', $user->id, $from->format('Y-m-d'), $to->format('Y-m-d'), $propertyId ?? 'all'),
            300,
            function () use ($from, $to, $propertyIds) {
                $invoices = Invoice::query()->whereBetween('due_date', [$from->toDateString(), $to->toDateString()])
                    ->when($propertyIds !== null, fn ($query) => $query->whereHas('lease.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])));

                // Agregat per bulan: 2 query, tanpa hydrasi model.
                $billedRows = (clone $invoices)
                    ->selectRaw("DATE_FORMAT(due_date, '%Y-%m') as ym, SUM(total) as total")
                    ->groupBy('ym')->pluck('total', 'ym');

                $collectedRows = InvoicePayment::query()
                    ->where('type', 'payment')->where('status', 'verified')
                    ->whereBetween('paid_at', [$from, $to])
                    ->when($propertyIds !== null, fn ($q) => $q->whereHas('invoice.lease.room', fn ($room) => $room->whereIn('property_id', $propertyIds ?: [0])))
                    ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as ym, SUM(amount) as total")
                    ->groupBy('ym')->pluck('total', 'ym');

                $rows = [];
                for ($date = $from->copy()->startOfMonth(); $date->lte($to); $date->addMonth()) {
                    $key = $date->format('Y-m');
                    $rows[] = [
                        'label' => $date->translatedFormat('M Y'),
                        'billed' => (float) ($billedRows[$key] ?? 0),
                        'collected' => (float) ($collectedRows[$key] ?? 0),
                    ];
                }

                return $rows;
            }
        );
    }

    public function receivableAging(User $user, ?int $propertyId = null): array
    {
        $propertyIds = $this->propertyIds($user, $propertyId);
        // Preload withSum agar balance_due tidak memicu 2 query per invoice (N+1).
        $invoices = Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])
            ->withSum('verifiedPayments as verified_payments_sum_amount', 'amount')
            ->withSum('verifiedRefunds as verified_refunds_sum_amount', 'amount')
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
