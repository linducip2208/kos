<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\ContactSubmission;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Testimonial;
use Tests\TestCase;

/**
 * Unit test seluruh model: relasi, accessor, scope, label helper.
 */
class ModelTest extends TestCase
{
    // ═══════════════════ PROPERTY ═══════════════════

    public function test_property_has_rooms_relation(): void
    {
        $property = Property::factory()->create();
        Room::factory()->count(3)->create(['property_id' => $property->id]);

        $this->assertCount(3, $property->fresh()->rooms);
    }

    public function test_property_has_room_types_relation(): void
    {
        $property = Property::factory()->create();
        RoomType::factory()->count(2)->create(['property_id' => $property->id]);

        $this->assertCount(2, $property->fresh()->roomTypes);
    }

    public function test_property_available_rooms_scope(): void
    {
        $property = Property::factory()->create();
        Room::factory()->count(3)->available()->create(['property_id' => $property->id]);
        Room::factory()->count(2)->occupied()->create(['property_id' => $property->id]);

        $this->assertCount(3, $property->availableRooms);
    }

    public function test_property_photos_cast_as_array(): void
    {
        $property = Property::factory()->create(['photos' => ['img1.jpg', 'img2.jpg']]);

        $this->assertIsArray($property->photos);
        $this->assertCount(2, $property->photos);
    }

    public function test_property_facilities_cast_as_array(): void
    {
        $property = Property::factory()->create(['facilities' => ['WiFi', 'AC', 'Parkir']]);

        $this->assertIsArray($property->facilities);
        $this->assertContains('WiFi', $property->facilities);
    }

    // ═══════════════════ ROOM TYPE ═══════════════════

    public function test_room_type_belongs_to_property(): void
    {
        $property = Property::factory()->create();
        $type = RoomType::factory()->create(['property_id' => $property->id]);

        $this->assertEquals($property->id, $type->property->id);
    }

    public function test_room_type_has_rooms(): void
    {
        $type = RoomType::factory()->create();
        Room::factory()->count(4)->create(['room_type_id' => $type->id]);

        $this->assertCount(4, $type->rooms);
    }

    public function test_room_type_all_price_fields_stored(): void
    {
        $type = RoomType::factory()->create([
            'base_price_daily'     => 80000,
            'base_price_weekly'    => 450000,
            'base_price_monthly'   => 1500000,
            'base_price_quarterly' => 4000000,
            'base_price_yearly'    => 15000000,
        ]);

        $this->assertEquals(80000,    $type->base_price_daily);
        $this->assertEquals(450000,   $type->base_price_weekly);
        $this->assertEquals(1500000,  $type->base_price_monthly);
        $this->assertEquals(4000000,  $type->base_price_quarterly);
        $this->assertEquals(15000000, $type->base_price_yearly);
    }

    // ═══════════════════ ROOM ═══════════════════

    public function test_room_belongs_to_property(): void
    {
        $property = Property::factory()->create();
        $room = Room::factory()->create(['property_id' => $property->id]);

        $this->assertEquals($property->id, $room->property->id);
    }

    public function test_room_belongs_to_room_type(): void
    {
        $type = RoomType::factory()->create();
        $room = Room::factory()->create(['room_type_id' => $type->id]);

        $this->assertEquals($type->id, $room->roomType->id);
    }

    public function test_room_effective_price_uses_room_override(): void
    {
        $type = RoomType::factory()->create([
            'base_price_daily'   => 50000,
            'base_price_monthly' => 1000000,
        ]);
        $room = Room::factory()->create([
            'room_type_id'  => $type->id,
            'price_daily'   => 75000,
            'price_monthly' => 1200000,
        ]);

        $this->assertEquals(75000,   $room->effective_price_daily);
        $this->assertEquals(1200000, $room->effective_price_monthly);
    }

    public function test_room_effective_price_falls_back_to_room_type(): void
    {
        $type = RoomType::factory()->create([
            'base_price_daily'   => 50000,
            'base_price_monthly' => 900000,
        ]);
        $room = Room::factory()->create([
            'room_type_id'  => $type->id,
            'price_daily'   => null,
            'price_monthly' => null,
        ]);

        $this->assertEquals(50000,  $room->effective_price_daily);
        $this->assertEquals(900000, $room->effective_price_monthly);
    }

    public function test_room_effective_facilities_merges_type_and_room(): void
    {
        $type = RoomType::factory()->create(['facilities' => ['WiFi', 'AC']]);
        $room = Room::factory()->create([
            'room_type_id' => $type->id,
            'facilities'   => ['Kamar Mandi Dalam'],
        ]);

        $effective = $room->effective_facilities;
        $this->assertContains('WiFi', $effective);
        $this->assertContains('AC', $effective);
        $this->assertContains('Kamar Mandi Dalam', $effective);
    }

    public function test_room_status_labels(): void
    {
        $room = Room::factory()->create(['status' => 'available']);
        $this->assertEquals('Tersedia', $room->status_label);
        $this->assertEquals('success',  $room->status_color);

        $room->status = 'occupied';
        $this->assertEquals('Terisi',  $room->status_label);
        $this->assertEquals('danger',  $room->status_color);

        $room->status = 'maintenance';
        $this->assertEquals('Maintenance', $room->status_label);
        $this->assertEquals('warning',     $room->status_color);
    }

    public function test_room_has_active_lease(): void
    {
        $room = Room::factory()->create();
        $occupant = Occupant::factory()->create();
        Lease::factory()->active()->create([
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
        ]);

        $this->assertNotNull($room->activeLease);
        $this->assertEquals('active', $room->activeLease->status);
    }

    // ═══════════════════ OCCUPANT ═══════════════════

    public function test_occupant_has_portal_access_when_active(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();

        $this->assertTrue($occupant->hasPortalAccess());
    }

    public function test_occupant_no_portal_access_when_inactive(): void
    {
        $occupant = Occupant::factory()->create(['portal_active' => false]);

        $this->assertFalse($occupant->hasPortalAccess());
    }

    public function test_occupant_set_portal_password(): void
    {
        $occupant = Occupant::factory()->create();
        $occupant->setPortalPassword('rahasia123');

        $this->assertNotNull($occupant->fresh()->portal_password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $occupant->fresh()->portal_password));
    }

    public function test_occupant_whatsapp_number_falls_back_to_phone(): void
    {
        $occupant = Occupant::factory()->create([
            'phone'     => '081234567890',
            'whatsapp'  => null,
        ]);

        $this->assertEquals('081234567890', $occupant->whatsapp_number);
    }

    public function test_occupant_has_leases(): void
    {
        $occupant = Occupant::factory()->create();
        Lease::factory()->count(2)->create(['occupant_id' => $occupant->id]);

        $this->assertCount(2, $occupant->leases);
    }

    public function test_occupant_active_lease_relation(): void
    {
        $occupant = Occupant::factory()->create();
        Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Lease::factory()->expired()->create(['occupant_id' => $occupant->id]);

        $this->assertNotNull($occupant->activeLease);
        $this->assertEquals('active', $occupant->activeLease->status);
    }

    // ═══════════════════ LEASE ═══════════════════

    public function test_lease_belongs_to_room_and_occupant(): void
    {
        $room     = Room::factory()->create();
        $occupant = Occupant::factory()->create();
        $lease    = Lease::factory()->create([
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
        ]);

        $this->assertEquals($room->id,     $lease->room->id);
        $this->assertEquals($occupant->id, $lease->occupant->id);
    }

    public function test_lease_days_until_expiry(): void
    {
        $lease = Lease::factory()->create([
            'end_date' => now()->addDays(15)->toDateString(),
            'status'   => 'active',
        ]);

        $this->assertEqualsWithDelta(15, $lease->days_until_expiry, 1);
    }

    public function test_lease_is_expiring_soon_within_30_days(): void
    {
        $lease = Lease::factory()->create([
            'end_date' => now()->addDays(20)->toDateString(),
            'status'   => 'active',
        ]);

        $this->assertTrue($lease->is_expiring_soon);
    }

    public function test_lease_not_expiring_soon_after_30_days(): void
    {
        $lease = Lease::factory()->create([
            'end_date' => now()->addDays(60)->toDateString(),
            'status'   => 'active',
        ]);

        $this->assertFalse($lease->is_expiring_soon);
    }

    public function test_lease_active_sets_room_to_occupied(): void
    {
        $room = Room::factory()->available()->create();
        Lease::factory()->create([
            'room_id' => $room->id,
            'status'  => 'active',
        ]);

        $this->assertEquals('occupied', $room->fresh()->status);
    }

    public function test_lease_expired_sets_room_to_available(): void
    {
        $room = Room::factory()->occupied()->create();
        $occupant = Occupant::factory()->create();
        Lease::factory()->expired()->create([
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
        ]);

        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_lease_has_invoices(): void
    {
        $lease = Lease::factory()->create();
        Invoice::factory()->count(3)->create(['lease_id' => $lease->id]);

        $this->assertCount(3, $lease->invoices);
    }

    // ═══════════════════ INVOICE ═══════════════════

    public function test_invoice_belongs_to_lease(): void
    {
        $lease   = Lease::factory()->create();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id]);

        $this->assertEquals($lease->id, $invoice->lease->id);
    }

    public function test_invoice_is_overdue_when_past_due_and_unpaid(): void
    {
        $invoice = Invoice::factory()->overdue()->create();

        $this->assertTrue($invoice->is_overdue);
    }

    public function test_invoice_not_overdue_when_paid(): void
    {
        $invoice = Invoice::factory()->paid()->create([
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        $this->assertFalse($invoice->is_overdue);
    }

    public function test_invoice_total_with_penalty(): void
    {
        $invoice = Invoice::factory()->create([
            'total'   => 1000000,
            'penalty' => 50000,
        ]);

        $this->assertEquals(1050000, $invoice->total_with_penalty);
    }

    // ═══════════════════ MAINTENANCE REQUEST ═══════════════════

    public function test_maintenance_priority_labels(): void
    {
        $req = MaintenanceRequest::factory()->make(['priority' => 'urgent']);
        $this->assertEquals('Urgent',  $req->priority_label);
        $this->assertEquals('danger',  $req->priority_color);

        $req->priority = 'high';
        $this->assertEquals('Tinggi',  $req->priority_label);
        $this->assertEquals('warning', $req->priority_color);

        $req->priority = 'medium';
        $this->assertEquals('Sedang', $req->priority_label);
        $this->assertEquals('info',   $req->priority_color);

        $req->priority = 'low';
        $this->assertEquals('Rendah',  $req->priority_label);
        $this->assertEquals('success', $req->priority_color);
    }

    public function test_maintenance_status_labels(): void
    {
        $req = MaintenanceRequest::factory()->make(['status' => 'open']);
        $this->assertEquals('Terbuka', $req->status_label);
        $this->assertEquals('danger',  $req->status_color);

        $req->status = 'resolved';
        $this->assertEquals('Selesai', $req->status_label);
        $this->assertEquals('success', $req->status_color);
    }

    // ═══════════════════ BOOKING REQUEST ═══════════════════

    public function test_booking_request_status_labels(): void
    {
        $br = BookingRequest::factory()->make(['status' => 'pending']);
        $this->assertEquals('Menunggu', $br->status_label);
        $this->assertEquals('warning',  $br->status_color);

        $br->status = 'approved';
        $this->assertEquals('Disetujui', $br->status_label);
        $this->assertEquals('success',   $br->status_color);

        $br->status = 'rejected';
        $this->assertEquals('Ditolak', $br->status_label);
        $this->assertEquals('danger',  $br->status_color);
    }

    // ═══════════════════ FAQ ═══════════════════

    public function test_faq_active_scope_orders_by_order(): void
    {
        $property = Property::factory()->create();
        Faq::factory()->create(['property_id' => $property->id, 'order' => 3, 'is_active' => true, 'question' => 'Q3', 'answer' => 'A']);
        Faq::factory()->create(['property_id' => $property->id, 'order' => 1, 'is_active' => true, 'question' => 'Q1', 'answer' => 'A']);
        Faq::factory()->create(['property_id' => $property->id, 'order' => 2, 'is_active' => true, 'question' => 'Q2', 'answer' => 'A']);

        $faqs = Faq::active()->get();
        $this->assertEquals('Q1', $faqs[0]->question);
        $this->assertEquals('Q2', $faqs[1]->question);
        $this->assertEquals('Q3', $faqs[2]->question);
    }

    public function test_faq_inactive_excluded_from_scope(): void
    {
        Faq::factory()->count(4)->create(['is_active' => true]);
        Faq::factory()->count(2)->inactive()->create();

        $this->assertCount(4, Faq::active()->get());
    }

    public function test_faq_belongs_to_property(): void
    {
        $property = Property::factory()->create();
        $faq = Faq::factory()->create(['property_id' => $property->id]);

        $this->assertEquals($property->id, $faq->property->id);
    }

    public function test_faq_property_id_can_be_null(): void
    {
        $faq = Faq::factory()->create(['property_id' => null]);

        $this->assertNull($faq->property);
    }

    // ═══════════════════ TESTIMONIAL ═══════════════════

    public function test_testimonial_active_scope(): void
    {
        Testimonial::factory()->count(3)->create(['is_active' => true]);
        Testimonial::factory()->count(2)->inactive()->create();

        $this->assertCount(3, Testimonial::active()->get());
    }

    public function test_testimonial_rating_cast_as_integer(): void
    {
        $t = Testimonial::factory()->create(['rating' => 5]);

        $this->assertIsInt($t->rating);
        $this->assertEquals(5, $t->rating);
    }

    // ═══════════════════ CONTACT SUBMISSION ═══════════════════

    public function test_contact_submission_status_labels(): void
    {
        $sub = ContactSubmission::factory()->create(['status' => 'new']);
        $this->assertEquals('Baru',    $sub->status_label);
        $this->assertEquals('warning', $sub->status_color);

        $sub->status = 'read';
        $this->assertEquals('Dibaca', $sub->status_label);
        $this->assertEquals('info',   $sub->status_color);

        $sub->status = 'replied';
        $this->assertEquals('Dibalas', $sub->status_label);
        $this->assertEquals('success', $sub->status_color);
    }

    public function test_contact_submission_belongs_to_property(): void
    {
        $property = Property::factory()->create();
        $sub = ContactSubmission::factory()->create(['property_id' => $property->id]);

        $this->assertEquals($property->id, $sub->property->id);
    }
}
