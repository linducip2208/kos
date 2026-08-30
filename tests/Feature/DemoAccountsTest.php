<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_documented_demo_accounts_are_seeded_with_the_documented_password(): void
    {
        $this->seed(UserSeeder::class);

        foreach ([
            'admin@kos.test' => 'super_admin',
            'owner@kos.test' => 'owner',
            'manager@kos.test' => 'property_manager',
            'auditor@kos.test' => 'auditor',
        ] as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertSame($role, $user->role);
            $this->assertTrue(Hash::check('password', $user->password));
            $this->assertTrue($user->is_active);
        }
    }
}
