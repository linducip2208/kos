<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Pusatkan otorisasi Filament: Resource/Page/Widget cukup deklarasikan
 * `$permission` (gate untuk lihat) dan opsional `$managePermission`
 * (gate untuk create/edit/delete). Default manage = '<prefix>.manage'.
 *
 * Owner & super_admin dibypass lewat Gate::before().
 */
trait AuthorizesAccess
{
    /** Gate untuk melihat resource/page/widget, mis. 'room.view'. */
    protected static ?string $permission = null;

    /** Gate untuk create/edit/delete. Null = diturunkan dari $permission. */
    protected static ?string $managePermission = null;

    public static function canViewAny(): bool
    {
        return Gate::allows(static::viewPermission());
    }

    public static function canCreate(): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canEdit(Model $record): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canDeleteAny(): bool
    {
        return Gate::allows(static::managePermission());
    }

    public static function canAccess(): bool
    {
        return Gate::allows(static::viewPermission());
    }

    public static function canView(Model $record): bool
    {
        return Gate::allows(static::viewPermission());
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $propertyIds = $user?->scopedPropertyIds();

        if (! $user || $propertyIds === null) {
            return $query;
        }

        $model = class_basename(static::getModel());
        $direct = ['Room', 'RoomType', 'Facility', 'Expense', 'BookingRequest', 'Announcement', 'VisitorLog'];
        if (in_array($model, $direct, true)) {
            return $query->whereIn('property_id', $propertyIds ?: [0]);
        }

        $relations = [
            'Lease' => 'room',
            'Invoice' => 'lease.room',
            'InvoicePayment' => 'invoice.lease.room',
            'PaymentTransaction' => 'invoice.lease.room',
            'Deposit' => 'lease.room',
            'UtilityReading' => 'room',
            'MaintenanceRequest' => 'room',
            'RoomTransfer' => 'fromRoom',
            'CheckinRecord' => 'room',
            'EContract' => 'lease.room',
            'Occupant' => 'leases.room',
        ];

        if (isset($relations[$model])) {
            return $query->whereHas($relations[$model], fn (Builder $relation) => $relation->whereIn('property_id', $propertyIds ?: [0]));
        }

        return $query;
    }

    protected static function viewPermission(): string
    {
        if (static::$permission !== null) {
            return static::$permission;
        }

        $map = [
            'User' => 'user.manage', 'Property' => 'property.view', 'Facility' => 'property.view',
            'Room' => 'room.view', 'RoomType' => 'room.view', 'RoomChecklist' => 'checkin.view',
            'RoomInventoryItem' => 'inventory.view', 'Occupant' => 'tenant.view', 'Lease' => 'lease.view',
            'EContract' => 'lease.view', 'CheckinRecord' => 'checkin.view', 'RoomTransfer' => 'checkin.view',
            'Deposit' => 'deposit.view', 'DepositTransaction' => 'deposit.view', 'Invoice' => 'invoice.view',
            'InvoicePayment' => 'payment.view', 'PaymentTransaction' => 'payment.view', 'Expense' => 'expense.view',
            'UtilityRate' => 'utility.view', 'UtilityReading' => 'utility.view',
            'MaintenanceRequest' => 'maintenance.view', 'Vendor' => 'vendor.manage', 'VisitorLog' => 'visitor.view',
            'BookingRequest' => 'booking.view', 'CrmActivity' => 'booking.view', 'Announcement' => 'website.manage',
            'MessageTemplate' => 'website.manage', 'BlogPost' => 'website.manage', 'BlogCategory' => 'website.manage',
            'Faq' => 'website.manage', 'Testimonial' => 'website.manage', 'ContactSubmission' => 'website.manage',
            'AuditLog' => 'audit.view', 'AutomationLog' => 'audit.view',
        ];

        return $map[class_basename(static::getModel())] ?? 'settings.manage';
    }

    protected static function managePermission(): string
    {
        if (static::$managePermission !== null) {
            return static::$managePermission;
        }

        $perm = static::viewPermission();

        // 'foo.view' → 'foo.manage'; fallback kalau tidak berakhiran .view.
        return str_ends_with($perm, '.view')
            ? substr($perm, 0, -5).'.manage'
            : ($perm !== '' ? $perm : 'settings.manage');
    }
}
