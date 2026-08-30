<?php

namespace App\Support;

/**
 * Matriks permission granular per role.
 * Permission dinilai lewat Laravel Gate — daftar di sini adalah sumber kebenaran.
 *
 * Role:
 *  super_admin       — bypass semua gate
 *  owner             — akses penuh data bisnis (semua property)
 *  property_manager  — operasional property yang ditugaskan
 *  finance           — tagihan, pembayaran, deposit, laporan keuangan
 *  cashier           — terima pembayaran & verifikasi bukti
 *  customer_service  — keluhan tenant, maintenance intake, visitor log
 *  marketing         — booking, lead, follow-up, promo, konten website
 *  maintenance       — work order teknis (tanpa revenue/profit)
 *  security          — check-in/out, verifikasi tamu (limited)
 *  auditor           — read-only seluruh modul + audit log
 */
class Permissions
{
    public const ROLES = [
        'super_admin'      => 'Super Admin',
        'owner'            => 'Owner',
        'property_manager' => 'Property Manager',
        'finance'          => 'Finance',
        'cashier'          => 'Cashier',
        'customer_service' => 'Customer Service',
        'marketing'        => 'Marketing',
        'maintenance'      => 'Maintenance',
        'security'         => 'Security',
        'auditor'          => 'Auditor / Viewer',
    ];

    /** Deskripsi role untuk UI manajemen pengguna. */
    public const ROLE_DESCRIPTIONS = [
        'super_admin'      => 'Akses teknis penuh tanpa batas termasuk sistem & plugin.',
        'owner'            => 'Pemilik bisnis — semua data bisnis, semua properti.',
        'property_manager' => 'Operasional harian properti yang ditugaskan.',
        'finance'          => 'Tagihan, pembayaran, deposit, refund, dan laporan keuangan.',
        'cashier'          => 'Terima pembayaran dan verifikasi bukti transfer.',
        'customer_service' => 'Keluhan penyewa, intake perbaikan, dan tamu.',
        'marketing'        => 'Booking, leads, follow-up, promo, dan konten website.',
        'maintenance'      => 'Work order teknis — tanpa akses data keuangan.',
        'security'         => 'Buku tamu dan verifikasi check-in/out.',
        'auditor'          => 'Akses baca seluruh modul + audit log (read-only).',
    ];

    public const PERMISSIONS = [
        // Dashboard
        'dashboard.view',

        // Property
        'property.view', 'property.manage',
        'room.view', 'room.manage',
        'availability.view',

        // Tenancy
        'tenant.view', 'tenant.manage',
        'lease.view', 'lease.manage',
        'checkin.view', 'checkin.manage',
        'deposit.view', 'deposit.manage',

        // Billing
        'invoice.view', 'invoice.manage',
        'payment.view', 'payment.record', 'payment.verify',
        'refund.manage',
        'reconciliation.view',

        // Utility
        'utility.view', 'utility.manage',

        // Operations
        'maintenance.view', 'maintenance.manage', 'maintenance.technician',
        'vendor.manage',
        'inventory.view', 'inventory.manage',
        'visitor.view', 'visitor.manage',

        // Booking & CRM
        'booking.view', 'booking.manage',

        // Finance reports
        'finance.report',          // revenue/profit/cashflow
        'expense.view', 'expense.manage',

        // Reports umum
        'report.view',

        // Website
        'website.manage',

        // System
        'user.manage',
        'role.view',
        'settings.manage',
        'audit.view',
        'system.plugins',
    ];

    /** role => permissions */
    public const MATRIX = [
        'owner' => ['*'],

        'property_manager' => [
            'dashboard.view', 'property.view', 'property.manage',
            'room.view', 'room.manage', 'availability.view',
            'tenant.view', 'tenant.manage', 'lease.view', 'lease.manage',
            'checkin.view', 'checkin.manage', 'deposit.view',
            'invoice.view', 'payment.view',
            'utility.view', 'utility.manage',
            'maintenance.view', 'maintenance.manage', 'vendor.manage',
            'inventory.view', 'inventory.manage',
            'booking.view', 'booking.manage',
            'report.view', 'expense.view', 'expense.manage',
        ],

        'finance' => [
            'dashboard.view', 'property.view', 'room.view',
            'tenant.view', 'lease.view',
            'invoice.view', 'invoice.manage',
            'payment.view', 'payment.record', 'payment.verify',
            'refund.manage', 'reconciliation.view',
            'deposit.view', 'deposit.manage',
            'utility.view',
            'finance.report', 'expense.view', 'expense.manage',
            'report.view',
        ],

        'cashier' => [
            'dashboard.view', 'room.view', 'tenant.view', 'lease.view',
            'invoice.view', 'payment.view', 'payment.record',
            'deposit.view',
        ],

        'customer_service' => [
            'dashboard.view', 'property.view', 'room.view',
            'tenant.view', 'lease.view',
            'maintenance.view', 'maintenance.manage',
            'inventory.view',
            'booking.view',
            'checkin.view',
        ],

        'marketing' => [
            'dashboard.view', 'property.view', 'room.view', 'availability.view',
            'booking.view', 'booking.manage',
            'website.manage',
            'tenant.view',
        ],

        'maintenance' => [
            'dashboard.view', 'room.view',
            'maintenance.view', 'maintenance.technician',
            'inventory.view',
        ],

        'security' => [
            'dashboard.view', 'room.view',
            'tenant.view', 'checkin.view',
            'visitor.view', 'visitor.manage',
        ],

        'auditor' => [
            'dashboard.view', 'property.view', 'room.view', 'availability.view',
            'tenant.view', 'lease.view', 'checkin.view', 'deposit.view',
            'invoice.view', 'payment.view', 'reconciliation.view',
            'utility.view', 'maintenance.view', 'inventory.view',
            'booking.view', 'finance.report', 'expense.view',
            'report.view', 'audit.view',
        ],
    ];

    public static function permissionsFor(string $role): array
    {
        $perms = self::MATRIX[$role] ?? [];

        return in_array('*', $perms, true) ? self::PERMISSIONS : $perms;
    }

    public static function exists(string $role): bool
    {
        return isset(self::ROLES[$role]);
    }
}
