<?php

namespace Tests\Feature;

use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\RoomResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_matrix_only_contains_canonical_roles_and_permissions(): void
    {
        $this->assertSame([], array_diff(array_keys(Permissions::MATRIX), array_keys(Permissions::ROLES)));

        foreach (Permissions::MATRIX as $permissions) {
            $this->assertSame([], array_diff($permissions, Permissions::PERMISSIONS, ['*']));
        }
    }

    public function test_legacy_roles_normalize_without_destroying_account(): void
    {
        $this->assertSame('property_manager', Permissions::normalizeRole('staff'));
        $this->assertSame('auditor', Permissions::normalizeRole('viewer'));
        $this->assertNull(Permissions::normalizeRole('unknown-role'));
    }

    public function test_security_boundary_is_enforced_at_resource_layer(): void
    {
        $security = User::factory()->create(['role' => 'security']);
        $auditor = User::factory()->create(['role' => 'auditor']);

        $this->actingAs($security);
        $this->assertTrue(RoomResource::canViewAny());
        $this->assertFalse(InvoiceResource::canViewAny());
        $this->assertFalse(UserResource::canViewAny());

        $this->actingAs($auditor);
        $this->assertTrue(InvoiceResource::canViewAny());
        $this->assertFalse(InvoiceResource::canCreate());
        $this->assertFalse(InvoiceResource::canDeleteAny());
        $this->assertFalse(UserResource::canViewAny());
    }
}
