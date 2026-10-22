<?php

namespace Tests\Feature\Api;

use App\Models\EContract;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Occupant;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kelengkapan API mobile (Flutter): booking, profil, pembayaran,
 * e-contract, maintenance detail/batal, device token, foto properti.
 */
class FlutterApiTest extends TestCase
{
    private Occupant $occupant;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->occupant = Occupant::factory()->withPortalAccess('secret123')->create();
        $this->token    = $this->occupant->createToken('test')->plainTextToken;
    }

    private function auth(): self
    {
        return $this->withToken($this->token);
    }

    private function ownLease(): Lease
    {
        $room = Room::factory()->create();
        return Lease::factory()->create([
            'occupant_id' => $this->occupant->id,
            'room_id'     => $room->id,
            'status'      => 'active',
        ]);
    }

    // ── Properti publik ──────────────────────────────────────────────────

    public function test_public_properties_include_photos_city_facilities(): void
    {
        Property::factory()->create([
            'photos'     => ['seed/mass/ext-01.jpg'],
            'facilities' => ['wifi', 'parkir'],
        ]);

        $this->getJson('/api/v1/public/properties')
            ->assertOk()
            ->assertJsonFragment(['city' => Property::first()->city])
            ->assertJsonStructure([['id', 'slug', 'photos', 'city', 'province', 'facilities']]);
    }

    public function test_public_property_detail_includes_photos_and_type_photos(): void
    {
        $prop = Property::factory()->create(['photos' => ['seed/mass/ext-01.jpg']]);

        $this->getJson('/api/v1/public/properties/'.$prop->slug)
            ->assertOk()
            ->assertJsonPath('photos.0', url('storage/seed/mass/ext-01.jpg'));
    }

    // ── Booking publik ───────────────────────────────────────────────────

    public function test_public_booking_store_returns_201(): void
    {
        $prop = Property::factory()->create();

        $this->postJson('/api/v1/public/bookings/'.$prop->slug, [
            'name'            => 'Calon Penghuni',
            'phone'           => '081200000001',
            'desired_move_in' => now()->addDays(5)->toDateString(),
            'billing_cycle'   => 'monthly',
        ])->assertCreated()
            ->assertJsonStructure(['message', 'id']);
    }

    public function test_public_booking_validates_required_fields(): void
    {
        $prop = Property::factory()->create();

        $this->postJson('/api/v1/public/bookings/'.$prop->slug, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'desired_move_in', 'billing_cycle']);
    }

    // ── E-Contract ───────────────────────────────────────────────────────

    public function test_contract_show_and_sign_via_token(): void
    {
        Storage::fake('local');
        $lease = $this->ownLease();
        $contract = app(\App\Services\EContractService::class)->generate($lease);
        $token = $contract->generateSignToken();

        $this->getJson('/api/v1/public/contract/'.$token)
            ->assertOk()
            ->assertJsonPath('status', 'draft');

        $this->getJson('/api/v1/public/contract/salah')
            ->assertNotFound();

        $this->post('/api/v1/public/contract/'.$token.'/sign', [
            'signature' => UploadedFile::fake()->image('sign.png'),
            'agree'     => true,
        ])->assertOk()
            ->assertJsonPath('status', 'sent');

        $this->assertNull($contract->fresh()->sign_token);
        $this->assertNotNull($contract->fresh()->occupant_signed_at);
    }

    // ── Profil penyewa ───────────────────────────────────────────────────

    public function test_tenant_can_update_profile(): void
    {
        $this->auth()->putJson('/api/v1/tenant/profile', [
            'name'  => 'Nama Baru',
            'phone' => '081299999999',
        ])->assertOk()
            ->assertJsonPath('occupant.name', 'Nama Baru');

        $this->assertSame('081299999999', $this->occupant->fresh()->phone);
    }

    public function test_tenant_can_change_password(): void
    {
        $this->auth()->postJson('/api/v1/tenant/profile/password', [
            'current_password'      => 'secret123',
            'password'              => 'baru1234',
            'password_confirmation' => 'baru1234',
        ])->assertOk();

        $this->auth()->postJson('/api/v1/tenant/profile/password', [
            'current_password'      => 'salah',
            'password'              => 'baru1234',
            'password_confirmation' => 'baru1234',
        ])->assertStatus(422);
    }

    // ── Maintenance ──────────────────────────────────────────────────────

    public function test_tenant_maintenance_show_cancel_and_photo_upload(): void
    {
        Storage::fake('public');
        $this->ownLease();

        $resp = $this->auth()->post('/api/v1/tenant/maintenance', [
            'title'       => 'AC bocor',
            'description' => 'Air menetes',
            'photos'      => [UploadedFile::fake()->image('ac.jpg')],
        ]);
        $id = $resp->assertCreated()
            ->assertJsonStructure(['message', 'id'])
            ->json('id');

        $this->auth()->getJson('/api/v1/tenant/maintenance/'.$id)
            ->assertOk()
            ->assertJsonCount(1, 'photos');

        // Milik penghuni lain → 404. Catatan: guard Sanctum di-memoize
        // per test, jadi token TIDAK diganti mid-test; sebagai gantinya
        // buat data milik orang lain lalu akses pakai token sendiri.
        $other = Occupant::factory()->withPortalAccess()->create();
        $otherRoom = Room::factory()->create();
        Lease::factory()->create(['occupant_id' => $other->id, 'room_id' => $otherRoom->id, 'status' => 'active']);
        $otherReq = MaintenanceRequest::factory()->create(['occupant_id' => $other->id, 'room_id' => $otherRoom->id]);
        $this->auth()->getJson('/api/v1/tenant/maintenance/'.$otherReq->id)
            ->assertNotFound();

        $this->auth()->postJson('/api/v1/tenant/maintenance/'.$id.'/cancel')
            ->assertOk();

        $this->assertSame('cancelled', MaintenanceRequest::find($id)->status);

        $this->auth()->postJson('/api/v1/tenant/maintenance/'.$id.'/cancel')
            ->assertStatus(422);
    }

    // ── Pembayaran ───────────────────────────────────────────────────────

    public function test_tenant_can_create_manual_payment_transaction(): void
    {
        $lease = $this->ownLease();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent']);

        $this->auth()->postJson('/api/v1/tenant/invoices/'.$invoice->id.'/pay')
            ->assertOk()
            ->assertJsonPath('manual', true)
            ->assertJsonStructure(['order_id', 'amount']);
    }

    public function test_tenant_cannot_pay_already_paid_invoice(): void
    {
        $lease = $this->ownLease();
        $invoice = Invoice::factory()->paid()->create(['lease_id' => $lease->id]);

        $this->auth()->postJson('/api/v1/tenant/invoices/'.$invoice->id.'/pay')
            ->assertStatus(422);
    }

    public function test_tenant_can_upload_payment_proof(): void
    {
        Storage::fake('public');
        $lease = $this->ownLease();
        $invoice = Invoice::factory()->create(['lease_id' => $lease->id, 'status' => 'sent', 'total' => 1000000]);

        $this->auth()->post('/api/v1/tenant/invoices/'.$invoice->id.'/proof', [
            'amount' => 500000,
            'proof'  => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertCreated()
            ->assertJsonStructure(['message', 'payment_id']);

        $payment = InvoicePayment::where('invoice_id', $invoice->id)->first();
        $this->assertSame('pending_verification', $payment->status);
        $this->assertNotNull($payment->proof_path);
    }

    // ── Device token ─────────────────────────────────────────────────────

    public function test_tenant_can_register_and_remove_device(): void
    {
        $this->auth()->postJson('/api/v1/tenant/devices', ['fcm_token' => 'fake-fcm-token-123'])
            ->assertOk();
        $this->assertSame('fake-fcm-token-123', $this->occupant->fresh()->fcm_token);

        $this->auth()->deleteJson('/api/v1/tenant/devices')
            ->assertOk();
        $this->assertNull($this->occupant->fresh()->fcm_token);
    }
}
