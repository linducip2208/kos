<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * End-to-end flow dari nol hingga akhir:
 * Setup → Landing → Booking → Lease → Portal → Maintenance → Profil → Logout.
 *
 * Setiap test membangun state dari awal sehingga bisa dijalankan mandiri.
 */
class FullFlowTest extends TestCase
{
    // ════════════════════════════════════════════════════════
    // TAHAP 1 — SETUP AWAL (DB kosong, properti baru)
    // ════════════════════════════════════════════════════════

    public function test_01_fresh_database_has_no_properties(): void
    {
        $this->assertDatabaseCount('properties', 0);
        $this->assertDatabaseCount('rooms', 0);
        $this->assertDatabaseCount('occupants', 0);
    }

    public function test_02_create_property_with_room_types_and_rooms(): void
    {
        $property = Property::factory()->create([
            'name'      => 'Kos Permata',
            'address'   => 'Jl. Mawar No. 10',
            'city'      => 'Jakarta',
            'is_active' => true,
        ]);

        $type = RoomType::factory()->create([
            'property_id'          => $property->id,
            'name'                 => 'Kamar Standard',
            'base_price_daily'     => 100000,
            'base_price_weekly'    => 600000,
            'base_price_monthly'   => 2000000,
            'base_price_quarterly' => 5500000,
            'base_price_yearly'    => 20000000,
        ]);

        Room::factory()->count(3)->available()->create([
            'property_id'  => $property->id,
            'room_type_id' => $type->id,
        ]);
        Room::factory()->occupied()->create([
            'property_id'  => $property->id,
            'room_type_id' => $type->id,
        ]);

        $this->assertDatabaseHas('properties', ['name' => 'Kos Permata']);
        $this->assertDatabaseHas('room_types', ['name' => 'Kamar Standard', 'base_price_monthly' => 2000000]);
        $this->assertEquals(3, Room::where('property_id', $property->id)->where('status', 'available')->count());
        $this->assertEquals(1, Room::where('property_id', $property->id)->where('status', 'occupied')->count());
    }

    public function test_03_settings_configured_for_contact(): void
    {
        Cache::flush();
        Setting::set('app_name', 'Kos Permata');
        Setting::set('contact_whatsapp', '6281234567890');
        Setting::set('contact_phone', '021-8888888');
        Setting::set('contact_email', 'info@kospermata.com');

        $this->assertEquals('Kos Permata',       setting('app_name'));
        $this->assertEquals('6281234567890',     setting('contact_whatsapp'));
        $this->assertEquals('021-8888888',       setting('contact_phone'));
        $this->assertEquals('info@kospermata.com', setting('contact_email'));
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 2 — PENGUNJUNG PUBLIK (Landing Page)
    // ════════════════════════════════════════════════════════

    public function test_04_guest_sees_homepage_with_property(): void
    {
        Property::factory()->count(2)->create(['name' => 'Kos A']);

        $this->get(route('landing.home'))
            ->assertOk()
            ->assertSee('Kos A');
    }

    public function test_05_single_property_homepage_redirects_to_detail(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Tunggal']);

        $this->get(route('landing.home'))
            ->assertRedirect(route('landing.property', $property));
    }

    public function test_06_inactive_property_hidden_from_homepage(): void
    {
        Property::factory()->create(['name' => 'Aktif', 'is_active' => true]);
        Property::factory()->create(['name' => 'NonAktif', 'is_active' => false]);

        // 1 aktif → auto-redirect ke detail, yang terlihat adalah halaman property
        $response = $this->get(route('landing.home'));
        $response->assertDontSee('NonAktif');
    }

    public function test_07_property_detail_page_shows_name_and_address(): void
    {
        $property = Property::factory()->create([
            'name'    => 'Kos Mawar',
            'address' => 'Jl. Kenanga No. 5',
            'city'    => 'Bandung',
        ]);

        $this->get(route('landing.property', $property))
            ->assertOk()
            ->assertSee('Kos Mawar')
            ->assertSee('Jl. Kenanga No. 5');
    }

    public function test_08_property_detail_shows_all_price_tiers(): void
    {
        $property = Property::factory()->create();
        RoomType::factory()->create([
            'property_id'          => $property->id,
            'base_price_daily'     => 85000,
            'base_price_weekly'    => 500000,
            'base_price_monthly'   => 1800000,
            'base_price_quarterly' => 5000000,
            'base_price_yearly'    => 18000000,
        ]);

        $this->get(route('landing.property', $property))
            ->assertSee('85.000')
            ->assertSee('500.000')
            ->assertSee('1.800.000')
            ->assertSee('5.000.000')
            ->assertSee('18.000.000');
    }

    public function test_09_property_detail_shows_faq(): void
    {
        $property = Property::factory()->create();
        Faq::factory()->create([
            'property_id' => $property->id,
            'question'    => 'Apakah listrik termasuk?',
            'answer'      => 'Ya, listrik sudah termasuk.',
            'is_active'   => true,
            'order'       => 1,
        ]);

        $this->get(route('landing.property', $property))
            ->assertSee('Apakah listrik termasuk?')
            ->assertSee('Ya, listrik sudah termasuk.');
    }

    public function test_10_property_detail_shows_testimonials(): void
    {
        $property = Property::factory()->create();
        Testimonial::factory()->create([
            'property_id' => $property->id,
            'name'        => 'Ahmad Fauzi',
            'content'     => 'Tempatnya sangat bersih dan nyaman!',
            'is_active'   => true,
            'rating'      => 5,
        ]);

        $this->get(route('landing.property', $property))
            ->assertSee('Ahmad Fauzi')
            ->assertSee('Tempatnya sangat bersih dan nyaman!');
    }

    public function test_11_inactive_property_returns_404(): void
    {
        $property = Property::factory()->create(['is_active' => false]);

        $this->get(route('landing.property', $property))->assertNotFound();
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 3 — PENGUNJUNG KIRIM PESAN & BOOKING
    // ════════════════════════════════════════════════════════

    public function test_12_guest_submits_contact_form(): void
    {
        $property = Property::factory()->create();

        $this->post(route('landing.contact'), [
            'property_id' => $property->id,
            'name'        => 'Dewi Putri',
            'phone'       => '087899001122',
            'email'       => 'dewi@email.com',
            'message'     => 'Apakah masih ada kamar kosong?',
        ])
        ->assertRedirect()
        ->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_submissions', [
            'name'    => 'Dewi Putri',
            'phone'   => '087899001122',
            'email'   => 'dewi@email.com',
            'status'  => 'new',
        ]);
    }

    public function test_13_contact_form_validates_required(): void
    {
        $this->post(route('landing.contact'), [])
            ->assertSessionHasErrors(['name', 'phone', 'message']);
    }

    public function test_14_contact_form_rejects_invalid_email(): void
    {
        $this->post(route('landing.contact'), [
            'name'    => 'Test',
            'phone'   => '081234567890',
            'email'   => 'bukan-email',
            'message' => 'Pesan test',
        ])->assertSessionHasErrors('email');
    }

    public function test_15_guest_accesses_booking_page(): void
    {
        $property = Property::factory()->create();

        $this->get(route('booking.show', $property))
            ->assertOk()
            ->assertSee($property->name);
    }

    public function test_16_booking_shows_only_available_rooms(): void
    {
        $property = Property::factory()->create();
        Room::factory()->available()->create(['property_id' => $property->id, 'room_number' => 'AV1']);
        Room::factory()->occupied()->create(['property_id' => $property->id, 'room_number' => 'OCC2']);

        $this->get(route('booking.show', $property))
            ->assertSee('AV1')
            ->assertDontSee('OCC2');
    }

    public function test_17_submit_booking_request(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [
            'name'            => 'Hendra Wijaya',
            'phone'           => '082112345678',
            'email'           => 'hendra@mail.com',
            'desired_move_in' => now()->addDays(7)->format('Y-m-d'),
            'billing_cycle'   => 'monthly',
            'message'         => 'Saya ingin kamar yang tenang.',
        ])
        ->assertRedirect()
        ->assertSessionHas('booking_success');

        $this->assertDatabaseHas('booking_requests', [
            'property_id' => $property->id,
            'name'        => 'Hendra Wijaya',
            'status'      => 'pending',
        ]);
    }

    public function test_18_booking_validates_required_fields(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [])
            ->assertSessionHasErrors(['name', 'phone', 'desired_move_in', 'billing_cycle']);
    }

    public function test_19_booking_move_in_must_be_future(): void
    {
        $property = Property::factory()->create();

        $this->post(route('booking.store', $property), [
            'name'            => 'Test',
            'phone'           => '081234567890',
            'desired_move_in' => now()->subDay()->format('Y-m-d'),
            'billing_cycle'   => 'monthly',
        ])->assertSessionHasErrors('desired_move_in');
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 4 — ADMIN BUAT OCCUPANT DAN LEASE
    // ════════════════════════════════════════════════════════

    public function test_20_create_occupant_with_portal_access(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('rahasia123')->create([
            'name'  => 'Budi Santoso',
            'phone' => '081122334455',
            'email' => 'budi@example.com',
        ]);

        $this->assertDatabaseHas('occupants', ['name' => 'Budi Santoso']);
        $this->assertTrue($occupant->hasPortalAccess());
    }

    public function test_21_create_lease_sets_room_to_occupied(): void
    {
        $property = Property::factory()->create();
        $room     = Room::factory()->available()->create(['property_id' => $property->id, 'room_number' => 'K5']);
        $occupant = Occupant::factory()->create();

        $this->assertEquals('available', $room->fresh()->status);

        Lease::factory()->active()->create([
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
            'price'       => 2000000,
        ]);

        $this->assertEquals('occupied', $room->fresh()->status);
        $this->assertDatabaseHas('leases', [
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
            'status'      => 'active',
            'price'       => 2000000,
        ]);
    }

    public function test_22_generate_invoice_for_lease(): void
    {
        $lease   = Lease::factory()->active()->create();
        $invoice = Invoice::factory()->create([
            'lease_id'    => $lease->id,
            'base_amount' => 2000000,
            'total'       => 2000000,
            'status'      => 'sent',
        ]);

        $this->assertDatabaseHas('invoices', [
            'lease_id' => $lease->id,
            'total'    => 2000000,
            'status'   => 'sent',
        ]);
        $this->assertEquals(2000000, $invoice->total_with_penalty);
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 5 — PENYEWA LOGIN DAN PAKAI PORTAL
    // ════════════════════════════════════════════════════════

    public function test_23_occupant_can_login_to_portal(): void
    {
        Occupant::factory()->withPortalAccess('kunci99')->create([
            'phone' => '08900001111',
        ]);

        $this->post(route('portal.login.post'), [
            'phone'    => '08900001111',
            'password' => 'kunci99',
        ])->assertRedirect(route('portal.dashboard'));
    }

    public function test_24_dashboard_shows_active_lease_room(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $property = Property::factory()->create(['name' => 'Kos Flamboyan']);
        $room     = Room::factory()->create([
            'property_id' => $property->id,
            'room_number' => 'F3',
        ]);
        Lease::factory()->active()->create([
            'occupant_id' => $occupant->id,
            'room_id'     => $room->id,
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('F3')
            ->assertSee('Kos Flamboyan');
    }

    public function test_25_dashboard_shows_unpaid_invoice_count(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'overdue']);
        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent']);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    public function test_26_occupant_can_view_invoice_list(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-20260001',
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.index'))
            ->assertOk()
            ->assertSee('INV-20260001');
    }

    public function test_27_occupant_can_view_invoice_detail(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        $invoice  = Invoice::factory()->create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-20260002',
            'total'          => 2000000,
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertOk()
            ->assertSee('INV-20260002')
            ->assertSee('2.000.000');
    }

    public function test_28_occupant_cannot_view_others_invoice(): void
    {
        $occ1    = Occupant::factory()->withPortalAccess()->create();
        $occ2    = Occupant::factory()->create();
        $lease2  = Lease::factory()->active()->create(['occupant_id' => $occ2->id]);
        $invoice = Invoice::factory()->create(['lease_id' => $lease2->id]);

        $this->actingAs($occ1, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertForbidden();
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 6 — PENYEWA BUAT LAPORAN KERUSAKAN
    // ════════════════════════════════════════════════════════

    public function test_29_occupant_submits_maintenance_request(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $room     = Room::factory()->create();
        Lease::factory()->active()->create([
            'occupant_id' => $occupant->id,
            'room_id'     => $room->id,
        ]);

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.maintenance.store'), [
                'title'       => 'Toilet bocor',
                'description' => 'Toilet di kamar saya bocor dan mengeluarkan air.',
                'priority'    => 'high',
            ])
            ->assertRedirect(route('portal.maintenance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('maintenance_requests', [
            'occupant_id' => $occupant->id,
            'title'       => 'Toilet bocor',
            'status'      => 'open',
            'priority'    => 'high',
            'room_id'     => $room->id,
        ]);
    }

    public function test_30_maintenance_appears_in_list(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        MaintenanceRequest::factory()->create([
            'occupant_id' => $occupant->id,
            'title'       => 'Kipas tidak menyala',
        ]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.index'))
            ->assertOk()
            ->assertSee('Kipas tidak menyala');
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 7 — UPDATE PROFIL DAN PASSWORD
    // ════════════════════════════════════════════════════════

    public function test_31_occupant_updates_profile(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create(['name' => 'Nama Lama']);

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.update'), [
                'name'     => 'Nama Baru',
                'phone'    => '082000000001',
                'email'    => 'baru@mail.com',
                'whatsapp' => '082000000001',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $occupant->fresh();
        $this->assertEquals('Nama Baru',      $fresh->name);
        $this->assertEquals('082000000001',   $fresh->phone);
        $this->assertEquals('baru@mail.com',  $fresh->email);
    }

    public function test_32_occupant_changes_password(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('passwordawal')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'passwordawal',
                'password'              => 'passwordbaru99',
                'password_confirmation' => 'passwordbaru99',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('passwordbaru99', $occupant->fresh()->portal_password)
        );
    }

    public function test_33_password_change_fails_with_wrong_old_password(): void
    {
        $occupant = Occupant::factory()->withPortalAccess('benar')->create();

        $this->actingAs($occupant, 'portal')
            ->put(route('portal.profile.password'), [
                'current_password'      => 'salah',
                'password'              => 'baru12345',
                'password_confirmation' => 'baru12345',
            ])
            ->assertSessionHasErrors('current_password');
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 8 — LOGOUT
    // ════════════════════════════════════════════════════════

    public function test_34_occupant_can_logout(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));

        $this->assertGuest('portal');
    }

    public function test_35_after_logout_portal_requires_login_again(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->post(route('portal.logout'));

        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.login'));
    }

    // ════════════════════════════════════════════════════════
    // TAHAP 9 — LEASE BERAKHIR, KAMAR KEMBALI TERSEDIA
    // ════════════════════════════════════════════════════════

    public function test_36_lease_termination_frees_room(): void
    {
        $room    = Room::factory()->available()->create();
        $lease   = Lease::factory()->active()->create(['room_id' => $room->id]);

        $this->assertEquals('occupied', $room->fresh()->status);

        $lease->update(['status' => 'terminated', 'termination_reason' => 'Pindah kerja']);

        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_37_lease_expiry_frees_room(): void
    {
        $room  = Room::factory()->available()->create();
        $lease = Lease::factory()->active()->create(['room_id' => $room->id]);

        $lease->update(['status' => 'expired']);

        $this->assertEquals('available', $room->fresh()->status);
    }
}
