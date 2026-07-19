<?php

namespace Database\Seeders;

use App\Models\EContract;
use App\Models\Lease;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EContractSeeder extends Seeder
{
    public function run(): void
    {
        $leases = Lease::where('status', 'active')->with(['occupant', 'room.property'])->get();

        foreach ($leases->take(4) as $i => $lease) {
            $status = match ($i) {
                0 => 'fully_signed',
                1 => 'owner_signed',
                2 => 'sent',
                default => 'draft',
            };

            $html = $this->buildHtml($lease);

            EContract::create([
                'lease_id'        => $lease->id,
                'contract_number' => 'KTR-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'content_html'    => $html,
                'status'          => $status,
                'owner_signature' => in_array($status, ['owner_signed', 'fully_signed']) ? 'data:image/png;base64,iVBORw0KGgo=' : null,
                'occupant_signature' => $status === 'fully_signed' ? 'data:image/png;base64,iVBORw0KGgo=' : null,
                'owner_signed_at'    => in_array($status, ['owner_signed', 'fully_signed']) ? now()->subDays(rand(1, 10)) : null,
                'occupant_signed_at' => $status === 'fully_signed' ? now()->subDays(rand(1, 5)) : null,
                'sign_token'         => $status === 'sent' ? Str::random(40) : null,
                'sign_token_expires_at' => $status === 'sent' ? now()->addDays(7) : null,
            ]);
        }
    }

    private function buildHtml(Lease $lease): string
    {
        $occupant = $lease->occupant;
        $room     = $lease->room;
        $property = $room->property;

        return "<h2>PERJANJIAN SEWA KAMAR KOS</h2>
<p>Pada hari ini telah disepakati perjanjian sewa kamar antara:</p>
<p><strong>PIHAK PERTAMA (Pemilik):</strong> {$property->name}</p>
<p><strong>PIHAK KEDUA (Penyewa):</strong> {$occupant->name} | KTP: {$occupant->id_number}</p>
<h3>Pasal 1 - Objek Sewa</h3>
<p>Pihak Pertama menyewakan kamar nomor <strong>{$room->room_number}</strong> di {$property->address} kepada Pihak Kedua.</p>
<h3>Pasal 2 - Masa Sewa</h3>
<p>Masa sewa mulai <strong>{$lease->start_date}</strong> sampai dengan <strong>{$lease->end_date}</strong>.</p>
<h3>Pasal 3 - Harga Sewa</h3>
<p>Harga sewa sebesar <strong>Rp " . number_format($lease->price, 0, ',', '.') . "</strong> per bulan.</p>
<h3>Pasal 4 - Deposit</h3>
<p>Deposit sebesar <strong>Rp " . number_format($lease->deposit, 0, ',', '.') . "</strong> dibayar di awal dan dikembalikan saat kontrak berakhir.</p>
<h3>Pasal 5 - Kewajiban Penyewa</h3>
<ul>
<li>Membayar sewa tepat waktu setiap tanggal {$lease->billing_date}</li>
<li>Menjaga kebersihan dan ketertiban</li>
<li>Tidak mengubah struktur bangunan tanpa izin</li>
<li>Melaporkan kerusakan fasilitas segera</li>
</ul>
<p>Demikian perjanjian ini dibuat dengan sebenar-benarnya.</p>";
    }
}
