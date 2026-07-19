<?php

namespace Tests\Feature\Api;

use App\Models\Occupant;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function test_login_returns_token(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('secret123')->create();

        $response = $this->postJson('/api/auth/login', [
            'phone'    => $occupant->phone,
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'occupant' => ['id', 'name', 'phone']]);
    }

    public function test_login_with_wrong_credentials_returns_401(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('correct')->create();

        $this->postJson('/api/auth/login', [
            'phone'    => $occupant->phone,
            'password' => 'wrong',
        ])->assertUnauthorized();
    }

    public function test_login_with_inactive_portal_returns_401(): void
    {
        $occupant = Occupant::factory()->create([
            'portal_active'   => false,
            'portal_password' => bcrypt('pass'),
        ]);

        $this->postJson('/api/auth/login', [
            'phone'    => $occupant->phone,
            'password' => 'pass',
        ])->assertUnauthorized();
    }

    public function test_me_endpoint_returns_occupant_data(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['name' => 'Joko Widodo']);
        $token    = $occupant->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/tenant/me')
            ->assertOk()
            ->assertJsonPath('occupant.name', 'Joko Widodo');
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/tenant/me')->assertUnauthorized();
    }

    public function test_logout_deletes_token(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $token    = $occupant->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/auth/logout')->assertOk();

        // Token row should be gone from DB
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
