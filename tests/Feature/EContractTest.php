<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Services\EContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EContractTest extends TestCase
{
    use RefreshDatabase;

    private const PNG_1PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_occupant_sign_first_sets_sent_then_owner_completes(): void
    {
        Storage::fake('local');
        $lease = Lease::factory()->create();
        $contract = app(EContractService::class)->generate($lease);

        app(EContractService::class)->signByOccupant($contract->fresh(), self::PNG_1PX);
        $this->assertSame('sent', $contract->fresh()->status);

        app(EContractService::class)->signByOwner($contract->fresh(), self::PNG_1PX);
        $this->assertSame('fully_signed', $contract->fresh()->status);
    }

    public function test_signing_final_or_expired_contract_is_rejected(): void
    {
        Storage::fake('local');
        $lease = Lease::factory()->create();
        $contract = app(EContractService::class)->generate($lease);
        $contract->update([
            'owner_signature' => 'x', 'occupant_signature' => 'y', 'status' => 'fully_signed',
        ]);

        $this->expectException(HttpException::class);
        app(EContractService::class)->signByOwner($contract->fresh(), self::PNG_1PX);
    }

    public function test_invalid_signature_payload_is_rejected(): void
    {
        Storage::fake('local');
        $lease = Lease::factory()->create();
        $contract = app(EContractService::class)->generate($lease);

        try {
            app(EContractService::class)->signByOwner($contract, 'bukan-base64-!!!');
            $this->fail('Signature invalid harus ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }
    }

    public function test_regenerate_resets_signatures_to_draft(): void
    {
        Storage::fake('local');
        $lease = Lease::factory()->create();
        $service = app(EContractService::class);
        $contract = $service->generate($lease);
        $service->signByOwner($contract->fresh(), self::PNG_1PX);
        $this->assertSame('owner_signed', $contract->fresh()->status);

        $regen = $service->generate($lease);
        $this->assertSame('draft', $regen->status);
        $this->assertNull($regen->owner_signature);
    }

    public function test_portal_sign_link_flow_with_token(): void
    {
        Storage::fake('local');
        $lease = Lease::factory()->create();
        $contract = app(EContractService::class)->generate($lease);
        app(EContractService::class)->signByOwner($contract->fresh(), self::PNG_1PX);

        $link = app(EContractService::class)->sendSignLink($contract->fresh());
        $token = basename($link);
        $this->assertNotEmpty($token);

        $this->get("/portal/contract/sign/{$token}")->assertOk();
        $this->get('/portal/contract/sign/token-salah')->assertNotFound();

        $this->post("/portal/contract/sign/{$token}", [
            'signature' => UploadedFile::fake()->image('ttd.png'),
            'agree' => '1',
        ])->assertOk();

        $contract = $contract->fresh();
        $this->assertSame('fully_signed', $contract->status);
        $this->assertNull($contract->sign_token);

        // Token sekali pakai.
        $this->get("/portal/contract/sign/{$token}")->assertNotFound();
    }
}
