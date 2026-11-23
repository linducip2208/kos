<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveDemoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_remove_demo_deactivates_demo_accounts_and_keeps_real_ones(): void
    {
        $demo = User::factory()->create(['email' => 'demo@kos.test', 'is_active' => true]);
        $real = User::factory()->create(['email' => 'owner@bisnis.com', 'is_active' => true]);

        $this->artisan('koskosan:remove-demo', ['--force' => true])->assertSuccessful();

        $this->assertFalse($demo->fresh()->is_active);
        $this->assertTrue($real->fresh()->is_active);
    }

    public function test_remove_demo_delete_option_removes_demo_accounts(): void
    {
        User::factory()->create(['email' => 'demo@kos.test']);

        $this->artisan('koskosan:remove-demo', ['--force' => true, '--delete' => true])->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => 'demo@kos.test']);
    }

    public function test_database_seeder_is_blocked_in_production(): void
    {
        $this->app['env'] = 'production';

        try {
            $this->expectException(\RuntimeException::class);
            (new DatabaseSeeder)->run();
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
