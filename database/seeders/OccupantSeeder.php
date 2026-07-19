<?php

namespace Database\Seeders;

use App\Models\Occupant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OccupantSeeder extends Seeder
{
    public function run(): void
    {
        $occupants = [
            [
                'name'            => 'Andi Prasetyo',
                'email'           => 'andi@gmail.com',
                'phone'           => '081234000001',
                'whatsapp'        => '081234000001',
                'id_number'       => '3273011001900001',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Sudirman No. 5, Bandung',
                'occupation'      => 'Mahasiswa',
                'workplace'       => 'Universitas Padjadjaran',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Siti Nurhaliza',
                'email'           => 'siti.nur@yahoo.com',
                'phone'           => '082345000002',
                'whatsapp'        => '082345000002',
                'id_number'       => '3273015505950002',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Diponegoro No. 18, Cimahi',
                'occupation'      => 'Karyawan Swasta',
                'workplace'       => 'PT Teknologi Maju',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Budi Santoso',
                'email'           => 'budi.s@gmail.com',
                'phone'           => '083456000003',
                'whatsapp'        => '083456000003',
                'id_number'       => '3273010808850003',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Asia Afrika No. 22, Bandung',
                'occupation'      => 'Wiraswasta',
                'workplace'       => 'Toko Elektronik Maju',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Dewi Lestari',
                'email'           => 'dewi.lestari@gmail.com',
                'phone'           => '084567000004',
                'whatsapp'        => '084567000004',
                'id_number'       => '3273014404920004',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Ciumbuleuit No. 8, Bandung',
                'occupation'      => 'Mahasiswa',
                'workplace'       => 'Institut Teknologi Bandung',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Rizky Firmansyah',
                'email'           => 'rizky.f@hotmail.com',
                'phone'           => '085678000005',
                'whatsapp'        => '085678000005',
                'id_number'       => '3273011212900005',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Pasteur No. 30, Bandung',
                'occupation'      => 'Karyawan Swasta',
                'workplace'       => 'Bank Mandiri Cabang Bandung',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Maya Anggraini',
                'email'           => 'maya.anggraini@gmail.com',
                'phone'           => '086789000006',
                'whatsapp'        => '086789000006',
                'id_number'       => '3273012020950006',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Dago No. 14, Bandung',
                'occupation'      => 'Mahasiswa',
                'workplace'       => 'Universitas Indonesia (kelas jauh)',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
            [
                'name'            => 'Hendra Gunawan',
                'email'           => 'hendra.g@gmail.com',
                'phone'           => '087890000007',
                'whatsapp'        => '087890000007',
                'id_number'       => '3273010303880007',
                'id_type'         => 'ktp',
                'address'         => 'Jl. Cihampelas No. 20, Bandung',
                'occupation'      => 'PNS',
                'workplace'       => 'Dinas Pendidikan Kota Bandung',
                'portal_password' => Hash::make('password'),
                'portal_active'   => true,
            ],
        ];

        foreach ($occupants as $data) {
            Occupant::create($data);
        }
    }
}
