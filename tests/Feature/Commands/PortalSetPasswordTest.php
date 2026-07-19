<?php

namespace Tests\Feature\Commands;

use App\Models\Occupant;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalSetPasswordTest extends TestCase
{
    public function test_sets_portal_password_for_known_phone(): void
    {
        $occupant = Occupant::factory()->create(['phone' => '081234000001']);

        $this->artisan('portal:set-password', [
            'phone'    => '081234000001',
            'password' => 'newpassword123',
        ])->assertSuccessful();

        $occupant->refresh();
        $this->assertTrue($occupant->portal_active);
        $this->assertTrue(Hash::check('newpassword123', $occupant->portal_password));
    }

    public function test_fails_for_unknown_phone(): void
    {
        $this->artisan('portal:set-password', [
            'phone'    => '089999999999',
            'password' => 'pass123',
        ])->assertFailed();
    }

    public function test_fails_for_short_password(): void
    {
        $occupant = Occupant::factory()->create(['phone' => '081234000002']);

        $this->artisan('portal:set-password', [
            'phone'    => '081234000002',
            'password' => '12345',
        ])->assertFailed();
    }
}
