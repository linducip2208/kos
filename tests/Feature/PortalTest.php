<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Test seluruh Portal Penyewa: auth, dashboard, invoice, maintenance, profil, keamanan.
 */
class PortalTest extends TestCase
{
    // ═══════════════════ AUTH — LOGIN ═══════════════════

    public function test_login_page_loads(): void
    {
        $this->get(route('portal.login'))->assertOk();
    }

    public function test_login_page_shows_form_fields(): void
    {
        $this->get(route('portal.login'))
            ->assertSee('name="phone"', false)
            ->assertSee('name="password"', false);
    }

    public function test_login_success_redirects_to_dashboard(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('password123')->create([
            'phone' => '081111111111',
        ]);

        $this->post(route('portal.login.post'), [
            'phone'    => '081111111111',
            'password' => 'password123',
        ])->assertRedirect(route('portal.dashboard'));
    }

    public function test_login_updates_last_login_timestamp(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('pass99')->create([
            'phone' => '082222222222',
        ]);

        $this->post(route('portal.login.post'), [
            'phone'    => '082222222222',
            'password' => 'pass99',
        ]);

        $this->assertNotNull($occupant->fresh()->portal_last_login);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Occupant::factory()->withPortalAccess('benar123')->create(['phone' => '083333333333']);

        $this->post(route('portal.login.post'), [
            'phone'    => '083333333333',
            'password' => 'salah999',
        ])->assertSessionHasErrors('phone');
    }

    public function test_login_fails_when_portal_inactive(): void
    {
        Occupant::factory()->create([
            'phone'           => '084444444444',
            'portal_password' => Hash::make('rahasia'),
            'portal_active'   => false,
        ]);

        $this->post(route('portal.login.post'), [
            'phone'    => '084444444444',
            'password' => 'rahasia',
        ])->assertSessionHasErrors('phone');
    }

    public function test_login_fails_with_nonexistent_phone(): void
    {
        $this->post(route('portal.login.post'), [
            'phone'    => '089999999999',
            'password' => 'apasaja',
        ])->assertSessionHasErrors('phone');
    }

    public function test_login_validates_required_fields(): void
    {
        $this->post(route('portal.login.post'), [])
            ->assertSessionHasErrors(['phone', 'password']);
    }

    public function test_already_logged_in_redirected_from_login_page(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.login'))
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_logout_clears_session_and_redirects(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('portal');
    }

    // ═══════════════════ AUTH — GUARD (proteksi halaman) ═══════════════════

    public function test_dashboard_requires_auth(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_invoices_requires_auth(): void
    {
        $this->get(route('portal.invoices.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_maintenance_requires_auth(): void
    {
        $this->get(route('portal.maintenance.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_profile_requires_auth(): void
    {
        $this->get(route('portal.profile.edit'))
            ->assertRedirect(route('portal.login'));
    }

    // ═══════════════════ DASHBOARD ═══════════════════

    public function test_dashboard_loads_for_authenticated_occupant(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    public function test_dashboard_shows_occupant_name(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['name' => 'Andi Nugraha']);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertSee('Andi Nugraha');
    }

    public function test_dashboard_shows_active_lease_info(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $property = Property::factory()->create(['name' => 'Kos Merpati']);
        $room     = Room::factory()->create([
            'property_id' => $property->id,
            'room_number' => 'B7',
        ]);
        Lease::factory()->active()->create([
            'occupant_id' => $occupant->id,
            'room_id'     => $room->id,
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertSee('B7')
            ->assertSee('Kos Merpati');
    }

    public function test_dashboard_shows_unpaid_invoices(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent', 'total' => 1500000]);
        Invoice::factory()->overdue()->create(['lease_id' => $lease->id]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    public function test_dashboard_shows_no_active_lease_gracefully(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    // ═══════════════════ INVOICE ═══════════════════

    public function test_invoice_list_loads(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.index'))
            ->assertOk();
    }

    public function test_invoice_list_shows_own_invoices(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-TAMPIL',
            'status'         => 'sent',
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.index'))
            ->assertSee('INV-TAMPIL');
    }

    public function test_invoice_detail_loads(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        $invoice  = Invoice::factory()->create(['lease_id' => $lease->id]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertOk();
    }

    public function test_invoice_detail_shows_amount(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        $invoice  = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'total'    => 1750000,
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertSee('1.750.000');
    }

    public function test_invoice_detail_forbidden_for_other_occupant(): void
    {
        $occupant1 = Occupant::factory()->withPortalAccess()->create();
        $occupant2 = Occupant::factory()->withPortalAccess()->create();
        $lease2    = Lease::factory()->active()->create(['occupant_id' => $occupant2->id]);
        $invoice   = Invoice::factory()->create(['lease_id' => $lease2->id]);

        $this->actingAs($occupant1, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertForbidden();
    }

    // ═══════════════════ MAINTENANCE ═══════════════════

    public function test_maintenance_list_loads(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.index'))
            ->assertOk();
    }

    public function test_maintenance_list_shows_own_requests(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        MaintenanceRequest::factory()->create([
            'occupant_id' => $occupant->id,
            'title'       => 'AC rusak di kamar saya',
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.index'))
            ->assertSee('AC rusak di kamar saya');
    }

    public function test_maintenance_create_page_loads(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.create'))
            ->assertOk();
    }

    public function test_maintenance_store_creates_record(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $room     = Room::factory()->create();
        Lease::factory()->active()->create([
            'occupant_id' => $occupant->id,
            'room_id'     => $room->id,
        ]);

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [
                'title'       => 'Lampu kamar mati',
                'description' => 'Lampu di kamar nomor saya mati sejak kemarin.',
                'priority'    => 'medium',
            ])
            ->assertRedirect(route('portal.maintenance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_requests', [
            'occupant_id' => $occupant->id,
            'title'       => 'Lampu kamar mati',
            'status'      => 'open',
            'priority'    => 'medium',
        ]);
    }

    public function test_maintenance_store_validates_required_fields(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [])
            ->assertSessionHasErrors(['title', 'description', 'priority']);
    }

    public function test_maintenance_store_validates_priority_enum(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [
                'title'       => 'Test',
                'description' => 'Test desc',
                'priority'    => 'invalid_priority',
            ])
            ->assertSessionHasErrors('priority');
    }

    public function test_maintenance_request_belongs_to_room_of_active_lease(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $room     = Room::factory()->create();
        Lease::factory()->active()->create([
            'occupant_id' => $occupant->id,
            'room_id'     => $room->id,
        ]);

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [
                'title'       => 'Pintu rusak',
                'description' => 'Pintu tidak bisa dikunci.',
                'priority'    => 'high',
            ]);

        $req = MaintenanceRequest::where('title', 'Pintu rusak')->first();
        $this->assertNotNull($req);
        $this->assertEquals($room->id, $req->room_id);
    }

    // ═══════════════════ PROFIL ═══════════════════

    public function test_profile_edit_page_loads(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.profile.edit'))
            ->assertOk();
    }

    public function test_profile_edit_shows_current_data(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create([
            'name'  => 'Rizky Pratama',
            'phone' => '085678901234',
            'email' => 'rizky@test.com',
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.profile.edit'))
            ->assertSee('Rizky Pratama')
            ->assertSee('085678901234')
            ->assertSee('rizky@test.com');
    }

    public function test_profile_update_saves_changes(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['name' => 'Lama']);

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'     => 'Nama Baru',
                'phone'    => '087654321098',
                'email'    => 'baru@test.com',
                'whatsapp' => '087654321098',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals('Nama Baru', $occupant->fresh()->name);
        $this->assertEquals('baru@test.com', $occupant->fresh()->email);
    }

    public function test_profile_update_validates_name_required(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'  => '',
                'phone' => '081234567890',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_profile_update_validates_phone_required(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'  => 'Test User',
                'phone' => '',
            ])
            ->assertSessionHasErrors('phone');
    }

    public function test_profile_update_validates_email_format(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'  => 'Test',
                'phone' => '081234567890',
                'email' => 'bukan-email-valid',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_change_password_success(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('passwordlama')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'passwordlama',
                'password'              => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('passwordbaru123', $occupant->fresh()->portal_password));
    }

    public function test_change_password_fails_with_wrong_current(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('benar')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'salah',
                'password'              => 'baru123456',
                'password_confirmation' => 'baru123456',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_change_password_fails_when_confirmation_mismatch(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('lama')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'lama',
                'password'              => 'baru12345',
                'password_confirmation' => 'berbeda678',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_change_password_requires_min_6_chars(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('lama')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'lama',
                'password'              => '123',
                'password_confirmation' => '123',
            ])
            ->assertSessionHasErrors('password');
    }
}
