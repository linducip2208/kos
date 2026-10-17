<?php

namespace Tests\Feature;

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PseoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_city_and_campus_return_404(): void
    {
        $this->get('/best/kota-fiktif-xyz')->assertNotFound();
        $this->get('/kos-di-kota-fiktif-xyz')->assertNotFound();
        $this->get('/kos-dekat/kampus-fiktif-xyz')->assertNotFound();
        $this->get('/kos-dekat/kampus-fiktif-xyz-di-kota-fiktif')->assertNotFound();
        $this->get('/fasilitas/fasilitas-fiktif-xyz')->assertNotFound();
        $this->get('/kos-untuk-tipe-fiktif')->assertNotFound();
        $this->get('/beli-aplikasi-kos/di/kota-fiktif-xyz')->assertNotFound();
    }

    public function test_known_city_filters_properties_by_city(): void
    {
        Property::factory()->create(['name' => 'Kos Bandung Asli', 'city' => 'Bandung']);
        Property::factory()->create(['name' => 'Kos Jakarta Lain', 'city' => 'Jakarta']);

        $res = $this->get('/best/bandung')->assertOk();
        $res->assertSee('Kos Bandung Asli');
        $res->assertDontSee('Kos Jakarta Lain');
    }

    public function test_known_campus_and_facility_pages_load(): void
    {
        $this->get('/kos-dekat/itb')->assertOk();
        $this->get('/kos-dekat-itb-di-bandung')->assertOk();
        $this->get('/fasilitas/ac')->assertOk();
        $this->get('/kos-harga-500rb-1jt')->assertOk();
        $this->get('/kos-untuk-putri')->assertOk();
    }

    public function test_alternatif_and_bandingkan_require_real_properties(): void
    {
        $this->get('/alternatif/slug-tidak-ada-xyz')->assertNotFound();
        $this->get('/bandingkan/a-tidak-ada-vs-b-tidak-ada')->assertNotFound();
        $this->get('/bandingkan')->assertOk();

        $a = Property::factory()->create(['name' => 'Kos Alpha']);
        $b = Property::factory()->create(['name' => 'Kos Beta']);

        $this->get('/alternatif/'.$a->slug)->assertOk()->assertSee('Kos Alpha');
        $this->get("/bandingkan/{$a->slug}-vs-{$b->slug}")->assertOk()->assertSee('Kos Alpha');
    }
}
