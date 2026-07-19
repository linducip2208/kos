<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Heavy demo data seeder.
 *  - 100 properties spread across Indonesia
 *  - 3 room types per property
 *  - 100 rooms per property (10 000 rooms total)
 *  - 1 000 occupants with portal access
 *  - ~1 000 active leases + ~500 expired leases for history
 *  - ~6-12 months of invoice history (paid/sent/overdue mix)
 *  - Random maintenance / booking / contact / testimonial / faq data
 *
 * Run with:  php artisan db:seed --class=MassDataSeeder
 */
class MassDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first() ?? User::create([
            'name'     => 'Admin Kos',
            'email'    => 'admin@kos.test',
            'password' => Hash::make('password'),
        ]);

        DB::disableQueryLog();
        ini_set('memory_limit', '1024M');

        $this->command->info('1/8 Membuat 100 properti…');
        $propertyIds = $this->seedProperties(100);

        $this->command->info('2/8 Membuat tipe kamar (3 per properti)…');
        $roomTypes = $this->seedRoomTypes($propertyIds);

        $this->command->info('3/8 Membuat 10.000 kamar…');
        $rooms = $this->seedRooms($propertyIds, $roomTypes);

        $this->command->info('4/8 Membuat 1.000 penyewa…');
        $occupantIds = $this->seedOccupants(1000);

        $this->command->info('5/8 Membuat 1.500 kontrak (1.000 aktif + 500 expired)…');
        $leases = $this->seedLeases($rooms, $occupantIds, $admin->id);

        $this->command->info('6/8 Membuat invoice 12 bulan terakhir…');
        $this->seedInvoices($leases);

        $this->command->info('7/8 Membuat data maintenance, booking, kontak…');
        $this->seedMisc($propertyIds, $rooms, $occupantIds);

        $this->command->info('8/8 Membuat testimoni & FAQ landing…');
        $this->seedTestimonialsAndFaqs($propertyIds);

        $this->command->info('Selesai. Login admin: admin@kos.test / password');
    }

    /* ───────────────────────────── helpers ───────────────────────────── */

    private array $cities = [
        ['Jakarta Pusat', 'DKI Jakarta'], ['Jakarta Selatan', 'DKI Jakarta'],
        ['Jakarta Barat', 'DKI Jakarta'], ['Jakarta Timur', 'DKI Jakarta'],
        ['Jakarta Utara', 'DKI Jakarta'], ['Bandung', 'Jawa Barat'],
        ['Bekasi', 'Jawa Barat'], ['Bogor', 'Jawa Barat'], ['Depok', 'Jawa Barat'],
        ['Cirebon', 'Jawa Barat'], ['Tasikmalaya', 'Jawa Barat'], ['Sukabumi', 'Jawa Barat'],
        ['Tangerang', 'Banten'], ['Tangerang Selatan', 'Banten'], ['Serang', 'Banten'],
        ['Semarang', 'Jawa Tengah'], ['Solo', 'Jawa Tengah'], ['Magelang', 'Jawa Tengah'],
        ['Pekalongan', 'Jawa Tengah'], ['Tegal', 'Jawa Tengah'], ['Salatiga', 'Jawa Tengah'],
        ['Yogyakarta', 'DI Yogyakarta'], ['Sleman', 'DI Yogyakarta'], ['Bantul', 'DI Yogyakarta'],
        ['Surabaya', 'Jawa Timur'], ['Malang', 'Jawa Timur'], ['Kediri', 'Jawa Timur'],
        ['Probolinggo', 'Jawa Timur'], ['Madiun', 'Jawa Timur'], ['Jember', 'Jawa Timur'],
        ['Denpasar', 'Bali'], ['Badung', 'Bali'], ['Gianyar', 'Bali'],
        ['Medan', 'Sumatera Utara'], ['Pematangsiantar', 'Sumatera Utara'],
        ['Padang', 'Sumatera Barat'], ['Bukittinggi', 'Sumatera Barat'],
        ['Pekanbaru', 'Riau'], ['Dumai', 'Riau'],
        ['Palembang', 'Sumatera Selatan'], ['Lubuklinggau', 'Sumatera Selatan'],
        ['Bandar Lampung', 'Lampung'], ['Metro', 'Lampung'],
        ['Jambi', 'Jambi'], ['Bengkulu', 'Bengkulu'], ['Banda Aceh', 'Aceh'],
        ['Pontianak', 'Kalimantan Barat'], ['Banjarmasin', 'Kalimantan Selatan'],
        ['Balikpapan', 'Kalimantan Timur'], ['Samarinda', 'Kalimantan Timur'],
        ['Makassar', 'Sulawesi Selatan'], ['Manado', 'Sulawesi Utara'],
        ['Kendari', 'Sulawesi Tenggara'], ['Palu', 'Sulawesi Tengah'],
        ['Mataram', 'Nusa Tenggara Barat'], ['Kupang', 'Nusa Tenggara Timur'],
        ['Ambon', 'Maluku'], ['Ternate', 'Maluku Utara'], ['Jayapura', 'Papua'],
        ['Sorong', 'Papua Barat'],
    ];

    private array $streets = [
        'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Diponegoro', 'Jl. Gatot Subroto',
        'Jl. Ahmad Yani', 'Jl. Mawar', 'Jl. Melati', 'Jl. Kenanga',
        'Jl. Anggrek', 'Jl. Cendana', 'Jl. Pahlawan', 'Jl. Veteran',
        'Jl. Pemuda', 'Jl. Cendrawasih', 'Jl. Garuda', 'Jl. Rajawali',
        'Jl. Kartini', 'Jl. Kamboja', 'Jl. Flamboyan', 'Jl. Nusa Indah',
        'Jl. Teratai', 'Jl. Bougenville', 'Jl. Mangga', 'Jl. Rambutan',
        'Jl. Durian', 'Jl. Kelapa', 'Jl. Pisang', 'Jl. Salak',
    ];

    private array $firstNames = [
        'Andi','Budi','Citra','Dewi','Eko','Fajar','Gita','Hendra','Indah','Joko',
        'Kartika','Lina','Maya','Nanda','Oka','Putri','Qori','Rama','Sari','Tegar',
        'Umar','Vina','Wahyu','Xena','Yuda','Zahra','Agus','Bayu','Cahyo','Dimas',
        'Erika','Faisal','Galih','Hasan','Irma','Joni','Kiki','Lukman','Mira','Nia',
        'Oki','Prita','Rina','Surya','Tika','Udin','Wati','Yanto','Anisa','Bintang',
    ];
    private array $lastNames = [
        'Saputra','Wijaya','Pratama','Lestari','Anggraini','Setiawan','Nugroho','Hidayat',
        'Permata','Susanti','Kurniawan','Maulana','Firmansyah','Sari','Yulianto','Rahmawati',
        'Handoko','Salim','Gunawan','Purnomo','Nurhaliza','Putra','Putri','Santoso',
    ];

    private array $facilitiesPool = ['wifi','parkir_motor','parkir_mobil','keamanan_24jam','dapur_bersama','laundry','cctv','akses_kartu','musholla','rooftop'];
    private array $roomFacilitiesPool = ['kasur','lemari','meja_belajar','kursi','ac','kipas_angin','km_dalam','km_luar','tv','kulkas','wastafel'];

    /* ───────────────────────── seeders ───────────────────────── */

    private function seedProperties(int $count): array
    {
        $rows = [];
        $now  = now();

        for ($i = 1; $i <= $count; $i++) {
            [$city, $province] = $this->cities[array_rand($this->cities)];
            $name   = 'Kos ' . ['Indah','Bahagia','Sejahtera','Mawar','Cendana','Permata','Anggrek','Harmoni','Asri','Kencana'][$i % 10] . ' ' . $city;
            $street = $this->streets[array_rand($this->streets)] . ' No. ' . rand(1, 250);

            $rows[] = [
                'name'        => $name . ' #' . $i,
                'address'     => $street . ', RT ' . str_pad((string) rand(1, 15), 2, '0', STR_PAD_LEFT) . '/RW ' . str_pad((string) rand(1, 10), 2, '0', STR_PAD_LEFT),
                'city'        => $city,
                'province'    => $province,
                'postal_code' => (string) rand(10000, 99999),
                'latitude'    => -8.5 + mt_rand(0, 1500) / 100,
                'longitude'   => 95 + mt_rand(0, 4500) / 100,
                'description' => "Kos nyaman di {$city}, dekat kampus dan pusat perbelanjaan. Lingkungan aman dengan akses transportasi mudah.",
                'facilities'  => json_encode(array_slice($this->facilitiesPool, 0, rand(4, 8))),
                'photos'      => json_encode([
                    "https://picsum.photos/seed/prop{$i}a/800/600",
                    "https://picsum.photos/seed/prop{$i}b/800/600",
                    "https://picsum.photos/seed/prop{$i}c/800/600",
                ]),
                'rules'       => "Jam malam 23.00\nTidak merokok di dalam kamar\nTamu lawan jenis hanya sampai 21.00",
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('properties')->insert($rows);

        return DB::table('properties')->orderByDesc('id')->limit($count)->pluck('id')->reverse()->values()->all();
    }

    private function seedRoomTypes(array $propertyIds): array
    {
        $rows = [];
        $now  = now();
        $types = [
            ['Standar', 800000,  ['kasur','lemari','meja_belajar','kipas_angin']],
            ['Deluxe',  1300000, ['kasur','lemari','meja_belajar','ac','km_dalam']],
            ['VIP',     2000000, ['kasur','lemari','meja_belajar','ac','km_dalam','tv','kulkas']],
        ];

        foreach ($propertyIds as $pid) {
            foreach ($types as [$name, $price, $facs]) {
                $rows[] = [
                    'property_id'           => $pid,
                    'name'                  => $name,
                    'description'           => "Tipe kamar {$name} dengan fasilitas memadai",
                    'size_sqm'              => $name === 'VIP' ? 24 : ($name === 'Deluxe' ? 18 : 12),
                    'base_price_monthly'    => $price + rand(-100000, 200000),
                    'base_price_quarterly'  => null,
                    'base_price_yearly'     => null,
                    'facilities'            => json_encode($facs),
                    'photos'                => json_encode(["https://picsum.photos/seed/rt{$pid}{$name}/600/400"]),
                    'max_occupants'         => $name === 'VIP' ? 2 : 1,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('room_types')->insert($chunk);
        }

        $byProperty = [];
        foreach (DB::table('room_types')->whereIn('property_id', $propertyIds)->get() as $rt) {
            $byProperty[$rt->property_id][$rt->name] = ['id' => $rt->id, 'price' => (float) $rt->base_price_monthly];
        }
        return $byProperty;
    }

    private function seedRooms(array $propertyIds, array $roomTypes): array
    {
        $rows = [];
        $now  = now();
        // Distribusi status: 10% occupied, 75% available, 10% maintenance, 5% reserved
        $statusPool = array_merge(
            array_fill(0, 10, 'occupied'),
            array_fill(0, 75, 'available'),
            array_fill(0, 10, 'maintenance'),
            array_fill(0, 5,  'reserved'),
        );

        foreach ($propertyIds as $pid) {
            $types = $roomTypes[$pid];
            for ($n = 1; $n <= 100; $n++) {
                $floor = (int) ceil($n / 25);                // 4 lantai, 25 kamar/lantai
                $tName = $n <= 60 ? 'Standar' : ($n <= 90 ? 'Deluxe' : 'VIP');
                $type  = $types[$tName];
                $price = $type['price'] + rand(-50000, 100000);

                $rows[] = [
                    'property_id'    => $pid,
                    'room_type_id'   => $type['id'],
                    'room_number'    => $floor . str_pad((string) ($n - ($floor - 1) * 25), 2, '0', STR_PAD_LEFT),
                    'name'           => null,
                    'floor'          => $floor,
                    'description'    => null,
                    'facilities'     => null,
                    'photos'         => json_encode(["https://picsum.photos/seed/room{$pid}-{$n}/600/400"]),
                    'price_monthly'  => $price,
                    'price_quarterly'=> $price * 3 - 100000,
                    'price_yearly'   => $price * 11,
                    'size_sqm'       => $tName === 'VIP' ? 24 : ($tName === 'Deluxe' ? 18 : 12),
                    'status'         => $statusPool[array_rand($statusPool)],
                    'last_cleaned_at'=> null,
                    'notes'          => null,
                    'is_active'      => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('rooms')->insert($chunk);
        }

        // Ambil daftar kamar yang baru dibuat (id, property_id, status, price)
        return DB::table('rooms')
            ->whereIn('property_id', $propertyIds)
            ->select('id', 'property_id', 'status', 'price_monthly')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function seedOccupants(int $count): array
    {
        $rows = [];
        $now  = now();
        $hash = Hash::make('password');

        for ($i = 1; $i <= $count; $i++) {
            $first = $this->firstNames[array_rand($this->firstNames)];
            $last  = $this->lastNames[array_rand($this->lastNames)];
            $name  = $first . ' ' . $last;
            $phone = '08' . rand(1, 9) . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT);

            $rows[] = [
                'name'                => $name,
                'email'               => Str::lower($first . $i) . '@mail.test',
                'phone'               => $phone,
                'whatsapp'            => $phone,
                'id_number'           => '32' . str_pad((string) rand(1, 99999999999999), 14, '0', STR_PAD_LEFT),
                'id_type'             => 'ktp',
                'id_photo'            => null,
                'selfie_photo'        => null,
                'address'             => 'Jl. ' . $this->streets[array_rand($this->streets)] . ' No. ' . rand(1, 200),
                'occupation'          => ['Mahasiswa','Karyawan Swasta','PNS','Wiraswasta','Freelancer'][array_rand([0,1,2,3,4])],
                'workplace'           => ['Universitas Indonesia','PT Telkom','Bank BCA','Pemda','Tokopedia','Gojek','Shopee','BUMN'][array_rand([0,1,2,3,4,5,6,7])],
                'emergency_contact'   => json_encode(['name' => 'Orang tua', 'phone' => '081' . rand(100000000, 999999999)]),
                'notes'               => null,
                'portal_password'     => $hash,
                'remember_token'      => null,
                'portal_last_login'   => null,
                'portal_active'       => true,
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('occupants')->insert($chunk);
        }

        return DB::table('occupants')->orderByDesc('id')->limit($count)->pluck('id')->reverse()->values()->all();
    }

    private function seedLeases(array $rooms, array $occupantIds, int $adminId): array
    {
        $occupiedRooms = array_values(array_filter($rooms, fn ($r) => $r['status'] === 'occupied'));
        $availableRooms = array_values(array_filter($rooms, fn ($r) => $r['status'] === 'available'));

        $leaseRows = [];
        $now = now();
        $seq = 1;

        // 1) Active leases: pasangkan setiap kamar occupied dengan satu occupant
        $activeRooms = array_slice($occupiedRooms, 0, min(count($occupiedRooms), count($occupantIds)));
        foreach ($activeRooms as $i => $room) {
            $monthsAgo = rand(1, 11);
            $start     = $now->copy()->subMonths($monthsAgo)->startOfMonth();
            $end       = $start->copy()->addYear();
            $leaseRows[] = [
                'room_id'       => $room['id'],
                'occupant_id'   => $occupantIds[$i],
                'lease_number'  => 'LSE-' . str_pad((string) $seq++, 6, '0', STR_PAD_LEFT),
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'price'         => $room['price_monthly'],
                'deposit'       => $room['price_monthly'],
                'deposit_returned' => 0,
                'billing_cycle' => 'monthly',
                'billing_date'  => 1,
                'status'        => 'active',
                'created_by'    => $adminId,
                'created_at'    => $start,
                'updated_at'    => $now,
            ];
        }

        // 2) Expired leases: pakai kamar available + occupants yang dipakai ulang (untuk historical revenue chart)
        $expiredCount = min(500, count($availableRooms), count($occupantIds));
        for ($i = 0; $i < $expiredCount; $i++) {
            $room      = $availableRooms[$i];
            $occupant  = $occupantIds[($i + 100) % count($occupantIds)]; // offset, pakai ulang occupant
            $endMonths = rand(1, 8);
            $duration  = rand(3, 12);
            $end       = $now->copy()->subMonths($endMonths)->endOfMonth();
            $start     = $end->copy()->subMonths($duration)->startOfMonth();
            $leaseRows[] = [
                'room_id'       => $room['id'],
                'occupant_id'   => $occupant,
                'lease_number'  => 'LSE-' . str_pad((string) $seq++, 6, '0', STR_PAD_LEFT),
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'price'         => $room['price_monthly'],
                'deposit'       => $room['price_monthly'],
                'deposit_returned' => $room['price_monthly'],
                'deposit_returned_at' => $end->toDateString(),
                'billing_cycle' => 'monthly',
                'billing_date'  => 1,
                'status'        => 'expired',
                'created_by'    => $adminId,
                'created_at'    => $start,
                'updated_at'    => $end,
            ];
        }

        foreach (array_chunk($leaseRows, 1000) as $chunk) {
            DB::table('leases')->insert($chunk);
        }

        // Reload leases for invoice generation
        return DB::table('leases')
            ->whereIn('lease_number', array_column($leaseRows, 'lease_number'))
            ->select('id', 'price', 'start_date', 'end_date', 'status')
            ->get()
            ->map(fn ($l) => (array) $l)
            ->all();
    }

    private function seedInvoices(array $leases): void
    {
        $rows = [];
        $now  = now();
        $seq  = 1;

        foreach ($leases as $lease) {
            $start  = \Carbon\Carbon::parse($lease['start_date']);
            $end    = \Carbon\Carbon::parse($lease['end_date']);
            $cursor = $start->copy()->startOfMonth();
            $stop   = $lease['status'] === 'expired'
                ? $end->copy()->startOfMonth()
                : $now->copy()->startOfMonth();

            while ($cursor->lte($stop)) {
                $periodStart = $cursor->copy();
                $periodEnd   = $cursor->copy()->endOfMonth();
                $dueDate     = $cursor->copy()->addDays(10);

                if ($lease['status'] === 'expired') {
                    $status = 'paid';
                    $paidAt = $dueDate->copy()->subDays(rand(1, 8));
                } elseif ($cursor->lt($now->copy()->subMonth()->startOfMonth())) {
                    // bulan-bulan sebelum bulan lalu = mostly paid
                    $r = rand(1, 100);
                    $status = $r <= 90 ? 'paid' : ($r <= 96 ? 'overdue' : 'sent');
                    $paidAt = $status === 'paid' ? $dueDate->copy()->subDays(rand(1, 5)) : null;
                } elseif ($cursor->isSameMonth($now->copy()->subMonth())) {
                    // bulan lalu: mix
                    $r = rand(1, 100);
                    $status = $r <= 60 ? 'paid' : ($r <= 85 ? 'overdue' : 'sent');
                    $paidAt = $status === 'paid' ? $dueDate->copy()->addDays(rand(0, 3)) : null;
                } else {
                    // bulan ini
                    $r = rand(1, 100);
                    $status = $r <= 30 ? 'paid' : 'sent';
                    $paidAt = $status === 'paid' ? $now->copy()->subDays(rand(0, 5)) : null;
                }

                $rows[] = [
                    'lease_id'       => $lease['id'],
                    'invoice_number' => 'INV-' . str_pad((string) $seq++, 7, '0', STR_PAD_LEFT),
                    'period_start'   => $periodStart->toDateString(),
                    'period_end'     => $periodEnd->toDateString(),
                    'due_date'       => $dueDate->toDateString(),
                    'base_amount'    => $lease['price'],
                    'additional_charges' => null,
                    'discount'       => 0,
                    'total'          => $lease['price'],
                    'penalty'        => 0,
                    'status'         => $status,
                    'paid_at'        => $paidAt,
                    'payment_method' => $status === 'paid' ? ['transfer','cash','qris','va'][array_rand([0,1,2,3])] : null,
                    'payment_ref'    => null,
                    'payment_channel'=> null,
                    'payment_gateway_data' => null,
                    'notes'          => null,
                    'sent_at'        => $periodStart->copy()->addDays(2),
                    'reminder_sent_at' => null,
                    'created_at'     => $periodStart->copy()->addDays(2),
                    'updated_at'     => $now,
                ];

                $cursor->addMonth();
            }

            // Flush batch periodik untuk hemat memori
            if (count($rows) >= 2000) {
                DB::table('invoices')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table('invoices')->insert($chunk);
            }
        }
    }

    private function seedMisc(array $propertyIds, array $rooms, array $occupantIds): void
    {
        $now = now();

        // Maintenance requests (300 entri)
        $maintRows = [];
        $issues = [
            ['AC tidak dingin', 'AC kurang dingin, perlu service.'],
            ['Kran bocor', 'Kran wastafel bocor terus menerus.'],
            ['Lampu mati', 'Lampu kamar mati, perlu ganti.'],
            ['WiFi lambat', 'Koneksi WiFi sering putus.'],
            ['Pintu rusak', 'Engsel pintu lemari rusak.'],
            ['Atap bocor', 'Atap kamar bocor saat hujan.'],
            ['Kunci macet', 'Kunci kamar susah diputar.'],
        ];
        for ($i = 0; $i < 300; $i++) {
            $room  = $rooms[array_rand($rooms)];
            [$title, $desc] = $issues[array_rand($issues)];
            $statusR = rand(1, 100);
            $status = $statusR <= 30 ? 'open' : ($statusR <= 50 ? 'in_progress' : ($statusR <= 60 ? 'waiting_parts' : ($statusR <= 95 ? 'resolved' : 'cancelled')));
            $maintRows[] = [
                'room_id'      => $room['id'],
                'occupant_id'  => $occupantIds[array_rand($occupantIds)],
                'assigned_to'  => null,
                'title'        => $title,
                'description'  => $desc,
                'photos'       => null,
                'priority'     => ['low','medium','medium','high','urgent'][array_rand([0,1,2,3,4])],
                'status'       => $status,
                'estimated_cost' => $status === 'resolved' ? rand(50000, 500000) : null,
                'actual_cost'  => $status === 'resolved' ? rand(50000, 500000) : null,
                'resolution_notes' => $status === 'resolved' ? 'Sudah ditangani teknisi.' : null,
                'resolution_photos' => null,
                'resolved_at'  => $status === 'resolved' ? $now->copy()->subDays(rand(1, 60)) : null,
                'created_at'   => $now->copy()->subDays(rand(1, 90)),
                'updated_at'   => $now,
            ];
        }
        foreach (array_chunk($maintRows, 500) as $chunk) {
            DB::table('maintenance_requests')->insert($chunk);
        }

        // Booking requests (150 entri)
        $bookRows = [];
        for ($i = 0; $i < 150; $i++) {
            $pid = $propertyIds[array_rand($propertyIds)];
            $first = $this->firstNames[array_rand($this->firstNames)];
            $last  = $this->lastNames[array_rand($this->lastNames)];
            $statusR = rand(1, 100);
            $status = $statusR <= 30 ? 'pending' : ($statusR <= 50 ? 'contacted' : ($statusR <= 70 ? 'approved' : ($statusR <= 85 ? 'converted' : 'rejected')));
            $bookRows[] = [
                'property_id'    => $pid,
                'room_id'        => null,
                'room_type_id'   => null,
                'name'           => $first . ' ' . $last,
                'email'          => Str::lower($first) . '@mail.test',
                'phone'          => '08' . rand(1, 9) . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'whatsapp'       => null,
                'desired_move_in'=> $now->copy()->addDays(rand(1, 60))->toDateString(),
                'billing_cycle'  => 'monthly',
                'message'        => 'Saya tertarik untuk sewa kamar di kos ini, mohon info lebih lanjut.',
                'status'         => $status,
                'admin_notes'    => null,
                'converted_to_lease_id' => null,
                'converted_to_occupant_id' => null,
                'created_at'     => $now->copy()->subDays(rand(1, 120)),
                'updated_at'     => $now,
            ];
        }
        foreach (array_chunk($bookRows, 500) as $chunk) {
            DB::table('booking_requests')->insert($chunk);
        }

        // Contact submissions (80 entri)
        $contactRows = [];
        for ($i = 0; $i < 80; $i++) {
            $pid = rand(0, 1) ? $propertyIds[array_rand($propertyIds)] : null;
            $first = $this->firstNames[array_rand($this->firstNames)];
            $statusR = rand(1, 100);
            $status = $statusR <= 40 ? 'new' : ($statusR <= 70 ? 'read' : 'replied');
            $contactRows[] = [
                'property_id' => $pid,
                'name'        => $first . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'phone'       => '08' . rand(1, 9) . str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'email'       => Str::lower($first) . '@mail.test',
                'subject'     => ['Tanya kamar tersedia','Booking','Pembayaran','Lain-lain'][array_rand([0,1,2,3])],
                'message'     => 'Halo, mohon info ketersediaan kamar dan harga sewa per bulan. Terima kasih.',
                'status'      => $status,
                'reply'       => $status === 'replied' ? 'Sudah kami balas via WhatsApp.' : null,
                'replied_at'  => $status === 'replied' ? $now->copy()->subDays(rand(1, 30)) : null,
                'ip_address'  => '192.168.' . rand(0, 255) . '.' . rand(0, 255),
                'created_at'  => $now->copy()->subDays(rand(1, 90)),
                'updated_at'  => $now,
            ];
        }
        DB::table('contact_submissions')->insert($contactRows);
    }

    private function seedTestimonialsAndFaqs(array $propertyIds): void
    {
        $now = now();

        // Testimonials (40 entri — sebagian global, sebagian per properti)
        $testRows = [];
        $contents = [
            'Kos ini sangat nyaman dan bersih, lokasinya juga strategis.',
            'Pengelola ramah dan responsif. Recommended!',
            'Fasilitas lengkap, WiFi cepat, kamar luas.',
            'Sudah 2 tahun di sini, betah banget. Lingkungan aman.',
            'Harga sewa sebanding dengan kualitas yang didapat.',
            'Dekat kampus dan banyak makanan, ideal buat mahasiswa.',
        ];
        for ($i = 0; $i < 40; $i++) {
            $first = $this->firstNames[array_rand($this->firstNames)];
            $testRows[] = [
                'property_id' => $i < 20 ? null : $propertyIds[array_rand($propertyIds)],
                'name'        => $first . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'occupation'  => ['Mahasiswa','Karyawan','Wiraswasta','Freelancer'][array_rand([0,1,2,3])],
                'avatar'      => null,
                'rating'      => rand(4, 5),
                'content'     => $contents[array_rand($contents)],
                'order'       => $i,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('testimonials')->insert($testRows);

        // FAQs
        $faqs = [
            ['Bagaimana cara booking kamar?', 'Anda bisa booking via tombol "Booking Sekarang" di halaman properti, atau hubungi WhatsApp kami.'],
            ['Berapa minimum sewa?', 'Minimum sewa adalah 1 bulan, dengan deposit 1x sewa bulanan.'],
            ['Apakah ada biaya tambahan?', 'Biaya listrik, air, dan WiFi sudah termasuk dalam harga sewa.'],
            ['Boleh bawa pasangan/teman?', 'Tamu diperbolehkan sampai pukul 21.00, di luar jam itu mohon konfirmasi pengelola.'],
            ['Cara pembayaran sewa?', 'Pembayaran via transfer bank, QRIS, atau payment gateway yang tersedia di portal penyewa.'],
            ['Kapan jatuh tempo tagihan?', 'Tagihan diterbitkan setiap awal bulan, jatuh tempo 10 hari setelah diterbitkan.'],
            ['Ada CCTV?', 'Ya, kos kami dilengkapi CCTV 24 jam di area umum dan akses pintu masuk.'],
            ['Boleh memelihara hewan?', 'Mohon konfirmasi terlebih dahulu kepada pengelola.'],
        ];
        $faqRows = [];
        foreach ($faqs as $i => [$q, $a]) {
            $faqRows[] = [
                'property_id' => null,
                'question'    => $q,
                'answer'      => $a,
                'order'       => $i,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        DB::table('faqs')->insert($faqRows);
    }
}
