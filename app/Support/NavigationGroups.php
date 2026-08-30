<?php

namespace App\Support;

final class NavigationGroups
{
    public const OPERATIONAL = 'Operasional';

    public const TENANCY = 'Penyewa & Sewa';

    public const FINANCE = 'Keuangan';

    public const BOOKINGS = 'Booking & Layanan';

    public const REPORTS = 'Laporan & Analitik';

    public const SETTINGS = 'Pengaturan';

    public static function all(): array
    {
        return [self::OPERATIONAL, self::TENANCY, self::FINANCE, self::BOOKINGS, self::REPORTS, self::SETTINGS];
    }
}
