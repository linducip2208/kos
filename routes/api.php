<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\AuthController as TenantAuthController;
use App\Http\Controllers\Api\DashboardController as TenantDashboardController;
use App\Http\Controllers\Api\InvoiceController as TenantInvoiceController;
use App\Http\Controllers\Api\LeaseController as TenantLeaseController;
use App\Http\Controllers\Api\MaintenanceController as TenantMaintenanceController;
use App\Http\Controllers\Api\PropertyController as PublicPropertyController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════
//  API v1
// ═══════════════════════════════════════════════════════════════════

Route::prefix('v1')->group(function () {

    // ── Public ───────────────────────────────────────────────────────
    Route::prefix('public')->group(function () {
        Route::get('properties',              [PublicPropertyController::class, 'index']);
        Route::get('properties/{property}',   [PublicPropertyController::class, 'show']);
    });

    // ── Admin Auth ────────────────────────────────────────────────────
    Route::post('admin/auth/login',  [Admin\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

        // Auth & profil
        Route::post  ('auth/logout',          [Admin\AuthController::class, 'logout']);
        Route::get   ('auth/me',              [Admin\AuthController::class, 'me']);
        Route::put   ('auth/profile',         [Admin\AuthController::class, 'updateProfile']);
        Route::post  ('auth/change-password', [Admin\AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('dashboard/summary',         [Admin\DashboardController::class, 'summary']);
        Route::get('dashboard/upcoming',         [Admin\DashboardController::class, 'upcoming']);
        Route::get('dashboard/recent-activity',  [Admin\DashboardController::class, 'recentActivity']);

        // Properties
        Route::apiResource('properties', Admin\PropertyController::class);

        // Rooms
        Route::get   ('rooms',              [Admin\RoomController::class, 'index']);
        Route::post  ('rooms',              [Admin\RoomController::class, 'store']);
        Route::get   ('rooms/available',    [Admin\RoomController::class, 'available']);
        Route::get   ('rooms/{room}',       [Admin\RoomController::class, 'show']);
        Route::put   ('rooms/{room}',       [Admin\RoomController::class, 'update']);
        Route::patch ('rooms/{room}/status',[Admin\RoomController::class, 'updateStatus']);
        Route::delete('rooms/{room}',       [Admin\RoomController::class, 'destroy']);

        // Occupants
        Route::apiResource('occupants', Admin\OccupantController::class);
        Route::get('occupants/{occupant}/leases', [Admin\OccupantController::class, 'leases']);

        // Leases — static routes must come before apiResource to avoid {lease} shadow
        Route::get  ('leases/expiring',          [Admin\LeaseController::class, 'expiring']);
        Route::apiResource('leases', Admin\LeaseController::class);
        Route::patch('leases/{lease}/terminate', [Admin\LeaseController::class, 'terminate']);
        Route::get  ('leases/{lease}/invoices',  [Admin\LeaseController::class, 'invoices']);

        // Invoices
        Route::apiResource('invoices', Admin\InvoiceController::class);
        Route::patch('invoices/{invoice}/mark-paid', [Admin\InvoiceController::class, 'markPaid']);

        // Users & Roles
        Route::apiResource('users', Admin\UserController::class)->except(['show']);

        // Settings
        Route::get ('settings',         [Admin\SettingController::class, 'index']);
        Route::put ('settings',         [Admin\SettingController::class, 'update']);
        Route::get ('settings/{group}', [Admin\SettingController::class, 'byGroup']);

        // License
        Route::get  ('license',          [Admin\LicenseController::class, 'info']);
        Route::post ('license/activate', [Admin\LicenseController::class, 'activate']);
        Route::post ('license/validate', [Admin\LicenseController::class, 'validate']);
        Route::post ('license/revoke',   [Admin\LicenseController::class, 'revoke']);

        // Reports
        Route::get('reports/occupancy',  [Admin\ReportController::class, 'occupancy']);
        Route::get('reports/revenue',    [Admin\ReportController::class, 'revenue']);
        Route::get('reports/overdue',    [Admin\ReportController::class, 'overdue']);
        Route::get('reports/turnover',   [Admin\ReportController::class, 'turnover']);

        // Plugins
        Route::get   ('plugins',                    [Admin\PluginController::class, 'index']);
        Route::post  ('plugins/install',            [Admin\PluginController::class, 'install']);
        Route::post  ('plugins/{slug}/activate',    [Admin\PluginController::class, 'activate']);
        Route::post  ('plugins/{slug}/deactivate',  [Admin\PluginController::class, 'deactivate']);
        Route::delete('plugins/{slug}',             [Admin\PluginController::class, 'destroy']);

        // Themes
        Route::get ('themes',          [Admin\ThemeController::class, 'index']);
        Route::get ('themes/active',   [Admin\ThemeController::class, 'active']);
        Route::post('themes/activate', [Admin\ThemeController::class, 'activate']);

        // Notifications
        Route::get  ('notifications',              [Admin\NotificationController::class, 'index']);
        Route::get  ('notifications/unread-count', [Admin\NotificationController::class, 'unreadCount']);
        Route::patch('notifications/{id}/read',    [Admin\NotificationController::class, 'markRead']);
        Route::post ('notifications/read-all',     [Admin\NotificationController::class, 'markAllRead']);

        // Export (Excel & PDF)
        Route::get('export',                           [Admin\ExportController::class, 'export']);
        Route::get('export/invoices/{invoice}/pdf',    [Admin\ExportController::class, 'invoicePdfSingle']);
    });

    // ── Tenant (Portal Penyewa) ───────────────────────────────────────
    Route::post('tenant/auth/login',  [TenantAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->prefix('tenant')->group(function () {
        Route::post ('auth/logout',   [TenantAuthController::class, 'logout']);
        Route::get  ('auth/me',       [TenantAuthController::class, 'me']);

        Route::get ('dashboard',      [TenantDashboardController::class, 'index']);
        Route::get ('lease',          [TenantLeaseController::class, 'active']);
        Route::get ('invoices',       [TenantInvoiceController::class, 'index']);
        Route::get ('invoices/{id}',  [TenantInvoiceController::class, 'show']);
        Route::get ('maintenance',    [TenantMaintenanceController::class, 'index']);
        Route::post('maintenance',    [TenantMaintenanceController::class, 'store']);
    });
});

// ── Legacy routes (backward compat, redirect ke v1) ──────────────────
Route::post('auth/login',  [TenantAuthController::class, 'login']);
Route::post('auth/logout', [TenantAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->prefix('tenant')->group(function () {
    Route::get('me',               [TenantAuthController::class, 'me']);
    Route::get('dashboard',        [TenantDashboardController::class, 'index']);
    Route::get('lease',            [TenantLeaseController::class, 'active']);
    Route::get('invoices',         [TenantInvoiceController::class, 'index']);
    Route::get('invoices/{id}',    [TenantInvoiceController::class, 'show']);
    Route::get('maintenance',      [TenantMaintenanceController::class, 'index']);
    Route::post('maintenance',     [TenantMaintenanceController::class, 'store']);
});
Route::get('properties',           [PublicPropertyController::class, 'index']);
Route::get('properties/{property}',[PublicPropertyController::class, 'show']);
