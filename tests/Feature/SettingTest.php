<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Test model Setting: get/set, tipe data, caching, helper setting().
 */
class SettingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_setting_get_returns_default_when_not_set(): void
    {
        $value = Setting::get('tidak_ada_kunci', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function test_setting_set_and_get_string(): void
    {
        Setting::set('app_name', 'Kos Bahagia');

        $this->assertEquals('Kos Bahagia', Setting::get('app_name'));
    }

    public function test_setting_set_overwrites_existing(): void
    {
        Setting::set('app_name', 'Pertama');
        Setting::set('app_name', 'Kedua');

        $this->assertEquals('Kedua', Setting::get('app_name'));
    }

    public function test_setting_boolean_type_true(): void
    {
        Setting::set('maintenance_mode', true, 'general', 'boolean');

        $value = Setting::get('maintenance_mode');
        $this->assertTrue($value);
    }

    public function test_setting_boolean_type_false(): void
    {
        Setting::set('maintenance_mode', false, 'general', 'boolean');

        $value = Setting::get('maintenance_mode');
        $this->assertFalse($value);
    }

    public function test_setting_json_type(): void
    {
        $data = ['key1' => 'val1', 'key2' => 42];
        Setting::set('some_config', $data, 'general', 'json');

        $result = Setting::get('some_config');
        $this->assertIsArray($result);
        $this->assertEquals('val1', $result['key1']);
        $this->assertEquals(42,     $result['key2']);
    }

    public function test_setting_different_groups_are_independent(): void
    {
        Setting::set('name', 'General Group', 'general');
        Setting::set('name', 'Payment Group', 'payment');

        $this->assertEquals('General Group', Setting::get('name', null, 'general'));
        $this->assertEquals('Payment Group', Setting::get('name', null, 'payment'));
    }

    public function test_setting_cache_is_cleared_after_set(): void
    {
        Setting::set('cached_key', 'pertama');
        $this->assertEquals('pertama', Setting::get('cached_key'));

        Setting::set('cached_key', 'kedua');
        $this->assertEquals('kedua', Setting::get('cached_key'));
    }

    public function test_global_setting_helper_function(): void
    {
        Setting::set('app_name', 'Kos Maju');

        $this->assertEquals('Kos Maju', setting('app_name'));
    }

    public function test_global_setting_helper_returns_default(): void
    {
        $this->assertEquals('Fallback', setting('tidak_ada', 'Fallback'));
    }

    public function test_setting_contact_keys(): void
    {
        Setting::set('contact_whatsapp', '6281234567890');
        Setting::set('contact_phone',    '0211234567');
        Setting::set('contact_email',    'admin@kos.com');
        Setting::set('contact_address',  'Jl. Contoh No. 1');

        $this->assertEquals('6281234567890', setting('contact_whatsapp'));
        $this->assertEquals('0211234567',    setting('contact_phone'));
        $this->assertEquals('admin@kos.com', setting('contact_email'));
        $this->assertEquals('Jl. Contoh No. 1', setting('contact_address'));
    }
}
