<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_user_cannot_export_financial_reports(): void
    {
        $user = User::factory()->create([
            'role' => 'maintenance',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('print.report.invoices'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('print.excel.invoices'))
            ->assertForbidden();
    }
}
