<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'app_name',         'value' => 'Kos Bahagia Manager', 'type' => 'string'],
            ['group' => 'general', 'key' => 'owner_name',       'value' => 'Budi Santoso',         'type' => 'string'],
            ['group' => 'general', 'key' => 'owner_phone',      'value' => '081234567890',          'type' => 'string'],
            ['group' => 'general', 'key' => 'owner_email',      'value' => 'budi@kosbahagia.id',    'type' => 'string'],
            ['group' => 'general', 'key' => 'invoice_prefix',   'value' => 'INV',                   'type' => 'string'],
            ['group' => 'general', 'key' => 'lease_prefix',     'value' => 'LSE',                   'type' => 'string'],
            ['group' => 'general', 'key' => 'reminder_days',    'value' => '3',                     'type' => 'integer'],
            ['group' => 'general', 'key' => 'late_fee_percent', 'value' => '5',                     'type' => 'integer'],
            // WhatsApp
            ['group' => 'whatsapp', 'key' => 'fonnte_token',   'value' => '',  'type' => 'string'],
            ['group' => 'whatsapp', 'key' => 'enabled',        'value' => '0', 'type' => 'boolean'],
            ['group' => 'whatsapp', 'key' => 'sender_number',  'value' => '',  'type' => 'string'],
            // Utility rates
            ['group' => 'utility', 'key' => 'electricity_rate', 'value' => '1500', 'type' => 'integer'],
            ['group' => 'utility', 'key' => 'water_rate',       'value' => '5000', 'type' => 'integer'],
            ['group' => 'utility', 'key' => 'gas_rate',         'value' => '8000', 'type' => 'integer'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['group' => $s['group'], 'key' => $s['key']],
                ['value' => $s['value'], 'type' => $s['type']]
            );
        }
    }
}
