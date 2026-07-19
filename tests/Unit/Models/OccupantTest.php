<?php

namespace Tests\Unit\Models;

use App\Models\Occupant;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OccupantTest extends TestCase
{
    public function test_can_create_occupant(): void
    {
        $occupant = Occupant::factory()->create(['name' => 'Budi Santoso']);
        $this->assertDatabaseHas('occupants', ['name' => 'Budi Santoso']);
    }

    public function test_portal_access_defaults_to_false(): void
    {
        $occupant = Occupant::factory()->create();
        $this->assertFalse($occupant->portal_active);
        $this->assertFalse($occupant->hasPortalAccess());
    }

    public function test_set_portal_password_enables_access(): void
    {
        $occupant = Occupant::factory()->create();
        $occupant->setPortalPassword('secret123');
        $occupant->update(['portal_active' => true]);
        $occupant->refresh();

        $this->assertTrue($occupant->hasPortalAccess());
        $this->assertTrue(Hash::check('secret123', $occupant->portal_password));
    }

    public function test_with_portal_access_factory_state(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('mypass')->create();
        $this->assertTrue($occupant->portal_active);
        $this->assertTrue(Hash::check('mypass', $occupant->portal_password));
    }

    public function test_auth_password_name_returns_portal_password(): void
    {
        $occupant = new Occupant();
        $this->assertEquals('portal_password', $occupant->getAuthPasswordName());
    }

    public function test_whatsapp_number_falls_back_to_phone(): void
    {
        $occupant = Occupant::factory()->create(['phone' => '081234567890', 'whatsapp' => null]);
        $this->assertEquals('081234567890', $occupant->whatsapp_number);
    }

    public function test_whatsapp_number_uses_whatsapp_field_when_set(): void
    {
        $occupant = Occupant::factory()->create(['phone' => '081111111111', 'whatsapp' => '082222222222']);
        $this->assertEquals('082222222222', $occupant->whatsapp_number);
    }
}
