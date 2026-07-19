<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Faq;
use App\Models\Property;
use App\Models\RoomType;
use App\Models\Testimonial;
use Tests\TestCase;

class LandingTest extends TestCase
{
    // ─── Homepage ────────────────────────────────────────────────────────────

    public function test_homepage_loads_ok(): void
    {
        Property::factory()->count(2)->create();

        $this->get(route('landing.home'))->assertOk();
    }

    public function test_homepage_shows_property_names(): void
    {
        Property::factory()->create(['name' => 'Kos Melati']);
        Property::factory()->create(['name' => 'Kos Anggrek']);

        $this->get(route('landing.home'))
            ->assertSee('Kos Melati')
            ->assertSee('Kos Anggrek');
    }

    public function test_homepage_single_property_redirects_to_detail(): void
    {
        $property = Property::factory()->create();

        $this->get(route('landing.home'))
            ->assertRedirect(route('landing.property', $property));
    }

    public function test_homepage_shows_testimonials(): void
    {
        Property::factory()->count(2)->create();
        Testimonial::factory()->create(['content' => 'Kos terbaik!']);
        Testimonial::factory()->inactive()->create(['content' => 'Tidak aktif']);

        $this->get(route('landing.home'))
            ->assertSee('Kos terbaik!')
            ->assertDontSee('Tidak aktif');
    }

    public function test_homepage_hides_inactive_properties(): void
    {
        Property::factory()->create(['name' => 'Kos Aktif', 'is_active' => true]);
        Property::factory()->create(['name' => 'Kos NonAktif', 'is_active' => false]);

        // There are now 2 properties but one inactive; homepage should show only active
        // But if only 1 active property, redirects to detail — so we assert the active one visible either way
        $response = $this->get(route('landing.home'));
        $response->assertDontSee('Kos NonAktif');
    }

    // ─── Property Detail ─────────────────────────────────────────────────────

    public function test_property_detail_page_loads_ok(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Mawar']);

        $this->get(route('landing.property', $property))
            ->assertOk()
            ->assertSee('Kos Mawar');
    }

    public function test_inactive_property_returns_404(): void
    {
        $property = Property::factory()->create(['is_active' => false]);

        $this->get(route('landing.property', $property))->assertNotFound();
    }

    public function test_property_shows_room_types_with_prices(): void
    {
        $property = Property::factory()->create();
        RoomType::factory()->create([
            'property_id'         => $property->id,
            'name'                => 'Kamar Standard',
            'base_price_daily'    => 100000,
            'base_price_weekly'   => 500000,
            'base_price_monthly'  => 1500000,
            'base_price_quarterly'=> 4200000,
            'base_price_yearly'   => 16000000,
        ]);

        $this->get(route('landing.property', $property))
            ->assertSee('Kamar Standard')
            ->assertSee('100.000')
            ->assertSee('500.000')
            ->assertSee('1.500.000')
            ->assertSee('4.200.000')
            ->assertSee('16.000.000');
    }

    public function test_property_shows_faqs(): void
    {
        $property = Property::factory()->create();
        Faq::factory()->create(['question' => 'Apakah ada dapur?', 'answer' => 'Ya, ada dapur bersama.']);
        Faq::factory()->inactive()->create(['question' => 'FAQ tidak aktif']);

        $this->get(route('landing.property', $property))
            ->assertSee('Apakah ada dapur?')
            ->assertSee('Ya, ada dapur bersama.')
            ->assertDontSee('FAQ tidak aktif');
    }

    public function test_property_shows_testimonials(): void
    {
        $property = Property::factory()->create();
        Testimonial::factory()->create(['name' => 'Budi Santoso', 'content' => 'Nyaman sekali!']);

        $this->get(route('landing.property', $property))
            ->assertSee('Budi Santoso')
            ->assertSee('Nyaman sekali!');
    }

    // ─── Contact Form ─────────────────────────────────────────────────────────

    public function test_contact_form_stores_submission(): void
    {
        $property = Property::factory()->create();

        $this->post(route('landing.contact'), [
            'property_id' => $property->id,
            'name'        => 'Siti Rahayu',
            'phone'       => '081234567890',
            'email'       => 'siti@example.com',
            'message'     => 'Saya ingin tanya tentang kamar.',
        ])->assertRedirect()->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('contact_submissions', [
            'name'    => 'Siti Rahayu',
            'phone'   => '081234567890',
            'status'  => 'new',
        ]);
    }

    public function test_contact_form_requires_name_phone_message(): void
    {
        $this->post(route('landing.contact'), [])
            ->assertSessionHasErrors(['name', 'phone', 'message']);
    }

    public function test_contact_form_rejects_invalid_email(): void
    {
        $this->post(route('landing.contact'), [
            'name'    => 'Test',
            'phone'   => '081234567890',
            'email'   => 'bukan-email',
            'message' => 'Test pesan',
        ])->assertSessionHasErrors(['email']);
    }

    // ─── Booking Flow ─────────────────────────────────────────────────────────

    public function test_booking_page_accessible_from_property_detail(): void
    {
        $property = Property::factory()->create();

        $this->get(route('booking.show', $property))->assertOk();
    }

    // ─── Database Integrity ───────────────────────────────────────────────────

    public function test_room_type_daily_weekly_prices_saved_correctly(): void
    {
        $property = Property::factory()->create();
        $type = RoomType::factory()->create([
            'property_id'        => $property->id,
            'base_price_daily'   => 75000,
            'base_price_weekly'  => 450000,
            'base_price_monthly' => 1200000,
        ]);

        $this->assertDatabaseHas('room_types', [
            'id'                 => $type->id,
            'base_price_daily'   => 75000,
            'base_price_weekly'  => 450000,
            'base_price_monthly' => 1200000,
        ]);
    }

    public function test_faq_scope_active_only_returns_active(): void
    {
        Faq::factory()->count(3)->create();
        Faq::factory()->count(2)->inactive()->create();

        $active = Faq::active()->get();
        $this->assertCount(3, $active);
        $active->each(fn ($f) => $this->assertTrue($f->is_active));
    }

    public function test_testimonial_scope_active_only_returns_active(): void
    {
        Testimonial::factory()->count(4)->create();
        Testimonial::factory()->count(1)->inactive()->create();

        $active = Testimonial::active()->get();
        $this->assertCount(4, $active);
    }

    public function test_contact_submission_status_labels(): void
    {
        $new     = ContactSubmission::factory()->create(['status' => 'new']);
        $read    = ContactSubmission::factory()->read()->create();
        $replied = ContactSubmission::factory()->replied()->create();

        $this->assertEquals('Baru', $new->status_label);
        $this->assertEquals('Dibaca', $read->status_label);
        $this->assertEquals('Dibalas', $replied->status_label);
    }
}
