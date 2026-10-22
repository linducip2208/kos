<?php

namespace Tests\Unit\Models;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Lease;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Agregat pembayaran Invoice: withSum preload harus identik dengan query langsung,
 * dan Resource tabel wajib mem-preload relasi (N+1 guard struktural).
 */
class InvoiceAggregatesTest extends TestCase
{
    use RefreshDatabase;

    private function makeInvoiceWithPayments(): Invoice
    {
        $lease = Lease::factory()->active()->create();
        $invoice = Invoice::factory()->create([
            'lease_id' => $lease->id,
            'total' => 1000000,
            'penalty' => 0,
            'status' => 'sent',
        ]);

        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'type' => 'payment',
            'amount' => 400000, 'method' => 'cash', 'status' => 'verified',
            'paid_at' => now(),
        ]);
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'type' => 'payment',
            'amount' => 300000, 'method' => 'transfer', 'status' => 'pending',
            'paid_at' => now(),
        ]);
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'type' => 'refund',
            'amount' => 100000, 'method' => 'cash', 'status' => 'verified',
            'paid_at' => now(),
        ]);

        return $invoice;
    }

    public function test_payable_and_paid_amounts_match_expected_values(): void
    {
        $invoice = $this->makeInvoiceWithPayments()->fresh();

        // payable = total + penalty − refund terverifikasi = 1.000.000 − 100.000
        $this->assertEquals(900000, $invoice->payable_amount);
        // paid = hanya payment terverifikasi = 400.000 (pending 300rb diabaikan)
        $this->assertEquals(400000, $invoice->paid_amount);
        $this->assertEquals(500000, $invoice->balance_due);
    }

    public function test_withsum_preload_gives_identical_aggregates(): void
    {
        $invoice = $this->makeInvoiceWithPayments();

        $plain = Invoice::find($invoice->id);
        $preloaded = Invoice::withSum('verifiedPayments', 'amount')
            ->withSum('verifiedRefunds', 'amount')
            ->find($invoice->id);

        $this->assertEquals($plain->paid_amount, $preloaded->paid_amount);
        $this->assertEquals($plain->payable_amount, $preloaded->payable_amount);
        $this->assertEquals($plain->balance_due, $preloaded->balance_due);
        $this->assertEquals(400000, $preloaded->verified_payments_sum_amount);
        $this->assertEquals(100000, $preloaded->verified_refunds_sum_amount);
    }

    public function test_invoice_resource_query_preloads_relations_and_sums(): void
    {
        $this->makeInvoiceWithPayments();

        $query = InvoiceResource::getEloquentQuery();
        $eager = array_keys($query->getEagerLoads());

        $this->assertContains('lease.occupant', $eager);
        $this->assertContains('lease.room', $eager);
        $this->assertNotEmpty($query->getQuery()->columns, 'withSum harus menambah kolom agregat');
    }
}
