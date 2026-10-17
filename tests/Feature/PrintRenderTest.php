<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Occupant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_download_invoice_and_receipt_pdf(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner', 'is_active' => true]));
        $lease = Lease::factory()->create(['status' => 'active']);
        $invoice = Invoice::factory()->paid()->create(['lease_id' => $lease->id]);

        $this->get(route('print.invoice', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('print.kwitansi', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('print.report.invoices'))->assertOk();
    }

    public function test_tenant_can_download_own_invoice_pdf(): void
    {
        $occupant = Occupant::factory()->withPortalAccess()->create();
        $lease = Lease::factory()->create(['occupant_id' => $occupant->id, 'status' => 'active']);
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id]);

        $this->actingAs($occupant, 'portal');

        $this->get(route('print.invoice', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
