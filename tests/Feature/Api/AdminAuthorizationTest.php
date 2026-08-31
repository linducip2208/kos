<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_cannot_read_admin_reports_or_invoices(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'maintenance',
            'is_active' => true,
        ]));

        $this->getJson('/api/v1/admin/reports/occupancy')->assertForbidden();
        $this->getJson('/api/v1/admin/invoices')->assertForbidden();
    }

    public function test_owner_can_read_empty_occupancy_report(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]));

        $this->getJson('/api/v1/admin/reports/occupancy')
            ->assertOk()
            ->assertJsonPath('total_rooms', 0);
    }

    public function test_maintenance_cannot_read_admin_properties_or_occupants(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'maintenance',
            'is_active' => true,
        ]));

        $this->getJson('/api/v1/admin/properties')->assertForbidden();
        $this->getJson('/api/v1/admin/occupants')->assertForbidden();
    }

    public function test_operational_roles_cannot_access_system_settings_plugins_themes_or_exports(): void
    {
        Sanctum::actingAs(User::factory()->create([
            'role' => 'maintenance',
            'is_active' => true,
        ]));

        $this->getJson('/api/v1/admin/settings')->assertForbidden();
        $this->getJson('/api/v1/admin/plugins')->assertForbidden();
        $this->getJson('/api/v1/admin/themes')->assertForbidden();
        $this->getJson('/api/v1/admin/license')->assertForbidden();
        $this->getJson('/api/v1/admin/export?type=invoices&format=csv')->assertForbidden();
    }
}
