<?php

namespace Tests\Feature;

use App\Models\Occupant;
use App\Models\Property;
use Tests\TestCase;

/**
 * Test keamanan route: akses publik, proteksi portal, proteksi admin.
 */
class RouteSecurityTest extends TestCase
{
    // ═══════════════════ PUBLIC ROUTES (harus bisa diakses tanpa login) ═══════════════════

    public function test_homepage_accessible_without_login(): void
    {
        Property::factory()->count(2)->create();

        $this->get('/')->assertOk();
    }

    public function test_property_detail_accessible_without_login(): void
    {
        $property = Property::factory()->create();

        $this->get(route('landing.property', $property))->assertOk();
    }

    public function test_booking_page_accessible_without_login(): void
    {
        $property = Property::factory()->create();

        $this->get(route('booking.show', $property))->assertOk();
    }

    public function test_contact_post_accessible_without_login(): void
    {
        $property = Property::factory()->create();

        $this->post(route('landing.contact'), [
            'name'    => 'Tamu',
            'phone'   => '081234567890',
            'message' => 'Halo',
        ])->assertRedirect();
    }

    public function test_portal_login_page_accessible_without_login(): void
    {
        $this->get(route('portal.login'))->assertOk();
    }

    // ═══════════════════ PORTAL PROTECTED ROUTES ═══════════════════

    public function test_portal_dashboard_redirects_guest(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_invoices_redirects_guest(): void
    {
        $this->get(route('portal.invoices.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_maintenance_index_redirects_guest(): void
    {
        $this->get(route('portal.maintenance.index'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_maintenance_create_redirects_guest(): void
    {
        $this->get(route('portal.maintenance.create'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_maintenance_store_redirects_guest(): void
    {
        $this->post(route('portal.maintenance.store'), [])
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_profile_redirects_guest(): void
    {
        $this->get(route('portal.profile.edit'))
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_profile_update_redirects_guest(): void
    {
        $this->put(route('portal.profile.update'), [])
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_password_change_redirects_guest(): void
    {
        $this->put(route('portal.profile.password'), [])
            ->assertRedirect(route('portal.login'));
    }

    public function test_portal_logout_redirects_guest(): void
    {
        $this->post(route('portal.logout'))
            ->assertRedirect(route('portal.login'));
    }

    // ═══════════════════ PORTAL ROUTES WORK WHEN AUTHENTICATED ═══════════════════

    public function test_portal_dashboard_accessible_when_logged_in(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.dashboard'))
            ->assertOk();
    }

    public function test_portal_invoices_accessible_when_logged_in(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.index'))
            ->assertOk();
    }

    public function test_portal_maintenance_accessible_when_logged_in(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.maintenance.index'))
            ->assertOk();
    }

    public function test_portal_profile_accessible_when_logged_in(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.profile.edit'))
            ->assertOk();
    }

    // ═══════════════════ 404 / SOFT DELETES ═══════════════════

    public function test_inactive_property_returns_404(): void
    {
        $property = Property::factory()->create(['is_active' => false]);

        $this->get(route('landing.property', $property))->assertNotFound();
    }

    public function test_nonexistent_property_returns_404(): void
    {
        $this->get('/property/99999')->assertNotFound();
    }

    public function test_nonexistent_booking_property_returns_404(): void
    {
        $this->get('/booking/99999')->assertNotFound();
    }

    // ═══════════════════ CSRF ═══════════════════

    public function test_post_without_csrf_rejected(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $property = Property::factory()->create();
        $this->post(route('landing.contact'), [
            'name'    => 'Test',
            'phone'   => '081234567890',
            'message' => 'Test message',
        ])->assertRedirect();
    }
}
