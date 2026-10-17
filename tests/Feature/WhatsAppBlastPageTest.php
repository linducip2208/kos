<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WhatsAppBlastPageTest extends TestCase
{
    public function test_owner_can_open_blast_page_and_maintenance_cannot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));
        $this->get('/admin/whatsapp-blast')->assertOk();

        $this->actingAs(User::factory()->create(['role' => 'maintenance', 'is_active' => true]));
        $this->get('/admin/whatsapp-blast')->assertForbidden();
    }

    public function test_inactive_user_cannot_access_admin_panel(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => false]));
        $this->get('/admin')->assertForbidden();
    }
}
