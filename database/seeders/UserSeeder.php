<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin Kos', 'email' => 'admin@kos.test', 'role' => 'super_admin'],
            ['name' => 'Owner Demo', 'email' => 'owner@kos.test', 'role' => 'owner'],
            ['name' => 'Property Manager Demo', 'email' => 'manager@kos.test', 'role' => 'property_manager'],
            ['name' => 'Auditor Demo', 'email' => 'auditor@kos.test', 'role' => 'auditor'],
        ] as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
        }
    }
}
