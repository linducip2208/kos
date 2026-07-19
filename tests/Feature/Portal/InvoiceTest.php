<?php

namespace Tests\Feature\Portal;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Occupant;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    public function test_invoice_list_requires_auth(): void
    {
        $this->get(route('portal.invoices.index'))->assertRedirect(route('portal.login'));
    }

    public function test_invoice_list_shows_only_own_invoices(): void
    {
        $occupant  = Occupant::factory()->withPortalAccess()->create();
        $lease     = Lease::factory()->create(['occupant_id' => $occupant->id]);
        $invoice   = Invoice::factory()->create(['lease_id' => $lease->id, 'invoice_number' => 'INV-MINE-001']);

        $other     = Occupant::factory()->withPortalAccess()->create();
        $otherLease   = Lease::factory()->create(['occupant_id' => $other->id]);
        $otherInvoice = Invoice::factory()->create(['lease_id' => $otherLease->id, 'invoice_number' => 'INV-OTHER-001']);

        $response = $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.index'));

        $response->assertOk()
            ->assertSee('INV-MINE-001')
            ->assertDontSee('INV-OTHER-001');
    }

    public function test_can_view_own_invoice_detail(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease    = Lease::factory()->create(['occupant_id' => $occupant->id]);
        $invoice  = Invoice::factory()->create(['lease_id' => $lease->id]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.show', $invoice))
            ->assertOk();
    }

    public function test_cannot_view_other_occupant_invoice(): void
    {
        $occupant      = Occupant::factory()->withPortalAccess()->create();
        $otherOccupant = Occupant::factory()->withPortalAccess()->create();
        $otherLease    = Lease::factory()->create(['occupant_id' => $otherOccupant->id]);
        $otherInvoice  = Invoice::factory()->create(['lease_id' => $otherLease->id]);

        $this->actingAs($occupant, 'portal')
            ->get(route('portal.invoices.show', $otherInvoice))
            ->assertForbidden();
    }
}
