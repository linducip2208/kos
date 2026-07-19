<?php

namespace Database\Seeders;

use App\Models\BookingRequest;
use App\Models\Property;
use Illuminate\Database\Seeder;

class BookingRequestSeeder extends Seeder
{
    public function run(): void
    {
        $p1 = Property::where('name', 'like', '%Pusat%')->first();
        $p2 = Property::where('name', 'like', '%Selatan%')->first();

        $requests = [
            [
                'property'        => $p1,
                'name'            => 'Ahmad Fauzan',
                'phone'           => '089612345001',
                'email'           => 'ahmad.fauzan@gmail.com',
                'whatsapp'        => '089612345001',
                'desired_move_in' => now()->addDays(7)->toDateString(),
                'billing_cycle'   => 'monthly',
                'message'         => 'Apakah tersedia kamar yang tidak terlalu mahal? Saya mahasiswa.',
                'status'          => 'pending',
            ],
            [
                'property'        => $p1,
                'name'            => 'Linda Susanti',
                'phone'           => '089712345002',
                'email'           => 'linda.s@yahoo.com',
                'whatsapp'        => '089712345002',
                'desired_move_in' => now()->addDays(14)->toDateString(),
                'billing_cycle'   => 'monthly',
                'message'         => 'Mau yang ada AC dan kamar mandi dalam. Budget sekitar 1,2 juta.',
                'status'          => 'contacted',
                'admin_notes'     => 'Sudah WA, tertarik kamar 105. Akan survei Sabtu ini.',
            ],
            [
                'property'        => $p2,
                'name'            => 'Doni Kusuma',
                'phone'           => '089812345003',
                'email'           => 'doni.k@gmail.com',
                'whatsapp'        => '089812345003',
                'desired_move_in' => now()->addDays(3)->toDateString(),
                'billing_cycle'   => 'monthly',
                'message'         => 'Ada parkir mobil? Saya butuh untuk kerja.',
                'status'          => 'approved',
                'admin_notes'     => 'Setuju kamar B2. DP sudah masuk.',
            ],
            [
                'property'        => $p1,
                'name'            => 'Rina Hartati',
                'phone'           => '089912345004',
                'email'           => 'rina.h@gmail.com',
                'whatsapp'        => '089912345004',
                'desired_move_in' => now()->addDays(21)->toDateString(),
                'billing_cycle'   => 'quarterly',
                'message'         => 'Mau pesan kamar VIP, ada yang kosong tidak?',
                'status'          => 'pending',
            ],
            [
                'property'        => $p2,
                'name'            => 'Wahyu Nugroho',
                'phone'           => '089512345005',
                'email'           => 'wahyu.n@gmail.com',
                'whatsapp'        => '089512345005',
                'desired_move_in' => now()->subDays(5)->toDateString(),
                'billing_cycle'   => 'monthly',
                'message'         => '',
                'status'          => 'rejected',
                'admin_notes'     => 'Tidak ada kamar yang sesuai budget. Disarankan cari properti lain.',
            ],
        ];

        foreach ($requests as $r) {
            if (!$r['property']) continue;
            BookingRequest::create([
                'property_id'    => $r['property']->id,
                'name'           => $r['name'],
                'phone'          => $r['phone'],
                'email'          => $r['email'],
                'whatsapp'       => $r['whatsapp'],
                'desired_move_in' => $r['desired_move_in'],
                'billing_cycle'  => $r['billing_cycle'],
                'message'        => $r['message'],
                'status'         => $r['status'],
                'admin_notes'    => $r['admin_notes'] ?? null,
            ]);
        }
    }
}
