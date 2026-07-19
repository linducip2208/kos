<?php

namespace Tests\Feature\Portal;

use App\Models\Occupant;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $this->get(route('portal.login'))->assertOk();
    }

    public function test_authenticated_user_redirected_from_login(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $this->actingAs($occupant, 'portal')
            ->get(route('portal.login'))
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_login_with_valid_credentials(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('pass1234')->create();

        $this->post(route('portal.login.post'), [
            'phone'    => $occupant->phone,
            'password' => 'pass1234',
        ])->assertRedirect(route('portal.dashboard'));

        $this->assertAuthenticatedAs($occupant, 'portal');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('correct')->create();

        $this->post(route('portal.login.post'), [
            'phone'    => $occupant->phone,
            'password' => 'wrong',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest('portal');
    }

    public function test_login_with_unknown_phone_fails(): void
    {
        $this->post(route('portal.login.post'), [
            'phone'    => '08999999999',
            'password' => 'anything',
        ])->assertSessionHasErrors('phone');
    }

    public function test_inactive_portal_account_cannot_login(): void
    {
        $occupant = Occupant::factory()->create([
            'portal_active'   => false,
            'portal_password' => bcrypt('pass123'),
        ]);

        $this->post(route('portal.login.post'), [
            'phone'    => $occupant->phone,
            'password' => 'pass123',
        ])->assertSessionHasErrors('phone');
    }

    public function test_logout_clears_session(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('portal');
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('portal.dashboard'))->assertRedirect(route('portal.login'));
    }
}
