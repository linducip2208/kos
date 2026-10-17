<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner', 'is_active' => true]);
    }

    public function test_revenue_empty_returns_zero_not_error(): void
    {
        Sanctum::actingAs($this->owner());

        $this->getJson('/api/v1/admin/reports/revenue')->assertOk()
            ->assertJsonPath('paid_count', 0)
            ->assertJsonPath('paid_total', 0)
            ->assertJsonPath('overdue_count', 0)
            ->assertJsonPath('overdue_total', 0)
            ->assertJsonCount(0, 'by_month');
    }

    public function test_revenue_counts_paid_and_overdue_with_monthly_breakdown(): void
    {
        Sanctum::actingAs($this->owner());
        $lease = Lease::factory()->create();
        Invoice::factory()->paid()->create(['lease_id' => $lease->id, 'total' => 1500000]);
        Invoice::factory()->overdue()->create(['lease_id' => $lease->id, 'total' => 750000]);

        $this->getJson('/api/v1/admin/reports/revenue')->assertOk()
            ->assertJsonPath('paid_count', 1)
            ->assertJsonPath('paid_total', 1500000)
            ->assertJsonPath('overdue_count', 1)
            ->assertJsonPath('overdue_total', 750000)
            ->assertJsonCount(1, 'by_month');
    }

    public function test_revenue_and_overdue_are_scoped_to_manager_property(): void
    {
        $propA = Property::factory()->create();
        $propB = Property::factory()->create();
        $leaseA = Lease::factory()->create(['room_id' => Room::factory()->create(['property_id' => $propA->id])->id]);
        $leaseB = Lease::factory()->create(['room_id' => Room::factory()->create(['property_id' => $propB->id])->id]);
        Invoice::factory()->paid()->create(['lease_id' => $leaseA->id, 'total' => 1000000]);
        Invoice::factory()->paid()->create(['lease_id' => $leaseB->id, 'total' => 9000000]);
        Invoice::factory()->overdue()->create(['lease_id' => $leaseB->id, 'total' => 500000]);

        $manager = User::factory()->create(['role' => 'property_manager', 'is_active' => true]);
        $manager->properties()->attach($propA);
        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/admin/reports/revenue')->assertOk()
            ->assertJsonPath('paid_total', 1000000)
            ->assertJsonPath('overdue_count', 0);

        $this->getJson('/api/v1/admin/reports/overdue')->assertOk()
            ->assertJsonPath('count', 0);

        $this->getJson('/api/v1/admin/reports/occupancy')->assertOk()
            ->assertJsonPath('total_rooms', 1);
    }

    public function test_turnover_lists_terminated_and_expired_in_range(): void
    {
        Sanctum::actingAs($this->owner());
        $terminated = Lease::factory()->create([
            'status' => 'terminated', 'terminated_at' => now()->subDays(5)->toDateString(),
        ]);
        $expired = Lease::factory()->create([
            'status' => 'expired',
            'start_date' => now()->subYear()->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
        ]);

        $res = $this->getJson('/api/v1/admin/reports/turnover')->assertOk();
        $res->assertJsonFragment(['id' => $terminated->id]);
        $res->assertJsonFragment(['id' => $expired->id]);
    }

    public function test_reports_reject_unauthorized_role(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'maintenance', 'is_active' => true]));

        foreach (['occupancy', 'revenue', 'overdue', 'turnover'] as $endpoint) {
            $this->getJson("/api/v1/admin/reports/{$endpoint}")->assertForbidden();
        }
    }
}
