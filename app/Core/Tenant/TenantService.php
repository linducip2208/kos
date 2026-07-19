<?php

namespace App\Core\Tenant;

class TenantService
{
    private static ?int $currentTenantId = null;

    public static function setCurrentTenant(?int $tenantId): void
    {
        static::$currentTenantId = $tenantId;
    }

    public static function getCurrentTenantId(): ?int
    {
        return static::$currentTenantId;
    }

    public static function isMultiTenant(): bool
    {
        return false;
    }
}
