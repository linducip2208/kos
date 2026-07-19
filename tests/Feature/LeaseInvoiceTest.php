<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use Tests\TestCase;

/**
 * Test alur Lease dan Invoice: observer status kamar, kalkulasi invoice, relasi lengkap.
 */
class LeaseInvoiceTest extends TestCase
{
    // ═══════════════════ LEASE OBSERVER ═══════════════════

    public function test_creating_active_lease_sets_room_occupied(): void
    {
        $room = Room::factory()->available()->create();
        $this->assertEquals('available', $room->fresh()->status);

        Lease::factory()->active()->create(['room_id' => $room->id]);

        $this->assertEquals('occupied', $room->fresh()->status);
    }

    public function test_creating_expired_lease_sets_room_available(): void
    {
        $room = Room::factory()->occupied()->create();
        $this->assertEquals('occupied', $room->fresh()->status);

        Lease::factory()->expired()->create(['room_id' => $room->id]);

        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_updating_lease_to_terminated_frees_room(): void
    {
        $room  = Room::factory()->available()->create();
        $lease = Lease::factory()->active()->create(['room_id' => $room->id]);

        $this->assertEquals('occupied', $room->fresh()->status);

        $lease->update(['status' => 'terminated']);

        $this->assertEquals('available', $room->fresh()->status);
    }

    public function test_updating_lease_back_to_active_re_occupies_room(): void
    {
        $room  = Room::factory()->available()->create();
        $lease = Lease::factory()->expired()->create(['room_id' => $room->id]);

        $lease->update(['status' => 'active']);

        $this->assertEquals('occupied', $room->fresh()->status);
    }

    // ═══════════════════ LEASE RELASI ═══════════════════

    public function test_lease_through_full_chain_property_room_type(): void
    {
        $property = Property::factory()->create(['name' => 'Kos Indah']);
        $type     = RoomType::factory()->create(['property_id' => $property->id, 'name' => 'Standard']);
        $room     = Room::factory()->create([
            'property_id'  => $property->id,
            'room_type_id' => $type->id,
            'room_number'  => 'A1',
        ]);
        $occupant = Occupant::factory()->create(['name' => 'Budi']);
        $lease    = Lease::factory()->active()->create([
            'room_id'     => $room->id,
            'occupant_id' => $occupant->id,
        ]);

        $this->assertEquals('A1',       $lease->room->room_number);
        $this->assertEquals('Kos Indah', $lease->room->property->name);
        $this->assertEquals('Standard',  $lease->room->roomType->name);
        $this->assertEquals('Budi',      $lease->occupant->name);
    }

    public function test_lease_number_is_unique(): void
    {
        $l1 = Lease::factory()->create();
        $l2 = Lease::factory()->create();

        $this->assertNotEquals($l1->lease_number, $l2->lease_number);
    }

    public function test_lease_expiry_attributes(): void
    {
        $soon = Lease::factory()->create([
            'end_date' => now()->addDays(10)->toDateString(),
            'status'   => 'active',
        ]);
        $far  = Lease::factory()->create([
            'end_date' => now()->addDays(90)->toDateString(),
            'status'   => 'active',
        ]);

        $this->assertTrue($soon->is_expiring_soon);
        $this->assertFalse($far->is_expiring_soon);
        $this->assertEqualsWithDelta(10, $soon->days_until_expiry, 1);
    }

    // ═══════════════════ INVOICE ═══════════════════

    public function test_invoice_linked_to_occupant_via_lease(): void
    {
        $occupant = Occupant::factory()->create(['name' => 'Siti']);
        $lease    = Lease::factory()->active()->create(['occupant_id' => $occupant->id]);
        Invoice::factory()->create(['lease_id' => $lease->id, 'total' => 1500000]);

        $invoices = $occupant->invoices;
        $this->assertCount(1, $invoices);
        $this->assertEquals(1500000, $invoices->first()->total);
    }

    public function test_invoice_overdue_flag(): void
    {
        $overdueInv = Invoice::factory()->overdue()->create();
        $sentInv    = Invoice::factory()->create([
            'due_date' => now()->addDays(10)->toDateString(),
            'status'   => 'sent',
        ]);

        $this->assertTrue($overdueInv->is_overdue);
        $this->assertFalse($sentInv->is_overdue);
    }

    public function test_invoice_paid_not_overdue_even_past_due(): void
    {
        $invoice = Invoice::factory()->paid()->create([
            'due_date' => now()->subDays(30)->toDateString(),
        ]);

        $this->assertFalse($invoice->is_overdue);
    }

    public function test_invoice_total_with_penalty_sums_correctly(): void
    {
        $invoice = Invoice::factory()->create([
            'total'   => 2000000,
            'penalty' => 100000,
        ]);

        $this->assertEquals(2100000, $invoice->total_with_penalty);
    }

    public function test_invoice_number_is_unique(): void
    {
        $i1 = Invoice::factory()->create();
        $i2 = Invoice::factory()->create();

        $this->assertNotEquals($i1->invoice_number, $i2->invoice_number);
    }

    public function test_multiple_invoices_per_lease(): void
    {
        $lease = Lease::factory()->create();
        Invoice::factory()->count(6)->create(['lease_id' => $lease->id]);

        $this->assertCount(6, $lease->invoices);
    }

    // ═══════════════════ OCCUPANT INVOICE QUERY ═══════════════════

    public function test_occupant_can_see_only_their_invoices(): void
    {
        $occ1 = Occupant::factory()->create();
        $occ2 = Occupant::factory()->create();

        $lease1 = Lease::factory()->create(['occupant_id' => $occ1->id]);
        $lease2 = Lease::factory()->create(['occupant_id' => $occ2->id]);

        Invoice::factory()->count(3)->create(['lease_id' => $lease1->id]);
        Invoice::factory()->count(2)->create(['lease_id' => $lease2->id]);

        $this->assertCount(3, $occ1->invoices);
        $this->assertCount(2, $occ2->invoices);
    }

    public function test_occupant_unpaid_invoices_query(): void
    {
        $occupant = Occupant::factory()->create();
        $lease    = Lease::factory()->create(['occupant_id' => $occupant->id]);

        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent']);
        Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'overdue']);
        Invoice::factory()->paid()->create(['lease_id' => $lease->id]);

        $unpaid = $occupant->invoices()
            ->whereIn('invoices.status', ['sent', 'overdue'])
            ->get();

        $this->assertCount(2, $unpaid);
    }
}
