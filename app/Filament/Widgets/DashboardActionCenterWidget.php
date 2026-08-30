<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BookingRequestResource;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\LeaseResource;
use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\BookingRequest;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Gate;

class DashboardActionCenterWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-action-center';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return Gate::allows('dashboard.view');
    }

    protected function getViewData(): array
    {
        $actions = [];
        if (Gate::allows('invoice.view')) {
            $count = Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])->where('due_date', '<', today())->count();
            if ($count) {
                $actions[] = ['label' => 'Invoice melewati jatuh tempo', 'count' => $count, 'tone' => 'danger', 'url' => InvoiceResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'overdue']]])];
            }
        }
        if (Gate::allows('lease.view')) {
            $count = Lease::query()->whereIn('status', ['active', 'expiring_soon'])->whereBetween('end_date', [today(), today()->addDays(30)])->count();
            if ($count) {
                $actions[] = ['label' => 'Kontrak perlu ditindaklanjuti', 'count' => $count, 'tone' => 'warning', 'url' => LeaseResource::getUrl('index')];
            }
        }
        if (Gate::allows('maintenance.view')) {
            $count = MaintenanceRequest::query()->whereNotIn('status', ['completed', 'closed', 'cancelled'])->where('sla_due_at', '<', now())->count();
            if ($count) {
                $actions[] = ['label' => 'Pemeliharaan melewati SLA', 'count' => $count, 'tone' => 'danger', 'url' => MaintenanceRequestResource::getUrl('index')];
            }
        }
        if (Gate::allows('booking.view')) {
            $count = BookingRequest::query()->whereIn('status', ['pending', 'inquiry', 'qualified', 'room_offered'])->count();
            if ($count) {
                $actions[] = ['label' => 'Booking menunggu respons', 'count' => $count, 'tone' => 'info', 'url' => BookingRequestResource::getUrl('index')];
            }
        }

        return ['actions' => $actions];
    }
}
