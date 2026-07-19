<?php

namespace Tests\Feature\Portal;

use App\Models\Occupant;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_profile_page_requires_auth(): void
    {
        $this->get(route('portal.profile.edit'))->assertRedirect(route('portal.login'));
    }

    public function test_profile_page_renders(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['name' => 'Dewi Lestari']);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.profile.edit'))
            ->assertOk()
            ->assertSee('Dewi Lestari');
    }

    public function test_can_update_profile(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'  => 'Nama Baru',
                'phone' => $occupant->phone,
                'email' => 'newemail@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('occupants', [
            'id'    => $occupant->id,
            'name'  => 'Nama Baru',
            'email' => 'newemail@example.com',
        ]);
    }

    public function test_can_change_password(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('oldpass')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'oldpass',
                'password'              => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpass123', $occupant->fresh()->portal_password));
    }

    public function test_wrong_current_password_fails(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('correct')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'wrong',
                'password'              => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_password_update_requires_confirmation(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'password123',
                'password'              => 'newpass',
                'password_confirmation' => 'different',
            ])
            ->assertSessionHasErrors('password');
    }
}
