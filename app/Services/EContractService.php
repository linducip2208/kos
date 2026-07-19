<?php

namespace App\Services;

use App\Models\EContract;
use App\Models\Lease;
use Carbon\Carbon;

class EContractService
{
    public function generate(Lease $lease): EContract
    {
        $lease->load(['occupant', 'room.property', 'room.roomType']);

        $existing = $lease->eContract;
        if ($existing && in_array($existing->status, ['fully_signed', 'owner_signed'])) {
            return $existing;
        }

        $contractNumber = 'EKTR-' . $lease->lease_number;
        $html           = $this->buildContractHtml($lease);

        return EContract::updateOrCreate(
            ['lease_id' => $lease->id],
            [
                'contract_number' => $contractNumber,
                'content_html'    => $html,
                'status'          => 'draft',
            ]
        );
    }

    public function signByOwner(EContract $contract, string $signatureBase64): void
    {
        $path = $this->saveSignature($signatureBase64, 'owner_' . $contract->id);
        $contract->update([
            'owner_signature' => $path,
            'owner_signed_at' => now(),
            'status'          => $contract->occupant_signature ? 'fully_signed' : 'owner_signed',
        ]);
    }

    public function signByOccupant(EContract $contract, string $signatureBase64): void
    {
        $path = $this->saveSignature($signatureBase64, 'occupant_' . $contract->id);
        $contract->update([
            'occupant_signature' => $path,
            'occupant_signed_at' => now(),
            'status'             => $contract->owner_signature ? 'fully_signed' : 'draft',
        ]);
    }

    public function sendSignLink(EContract $contract): string
    {
        $token = $contract->generateSignToken();
        return url("/portal/contract/sign/{$token}");
    }

    private function buildContractHtml(Lease $lease): string
    {
        $appName  = setting('app_name', 'Kos Manager');
        $occupant = $lease->occupant;
        $room     = $lease->room;
        $property = $room->property;

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: auto; padding: 40px;">
            <h2 style="text-align:center; text-transform:uppercase;">PERJANJIAN SEWA KAMAR</h2>
            <p style="text-align:center;">{$appName}</p>
            <hr>

            <p>Yang bertanda tangan di bawah ini:</p>

            <table style="width:100%; margin-bottom:20px;">
                <tr>
                    <td width="30%"><strong>PEMILIK KOS</strong></td>
                    <td>: {$appName}</td>
                </tr>
                <tr>
                    <td><strong>PENYEWA</strong></td>
                    <td>: {$occupant->name}</td>
                </tr>
                <tr>
                    <td><strong>No. KTP</strong></td>
                    <td>: {$occupant->id_number}</td>
                </tr>
                <tr>
                    <td><strong>No. HP</strong></td>
                    <td>: {$occupant->phone}</td>
                </tr>
            </table>

            <p>Sepakat mengadakan <strong>Perjanjian Sewa Kamar</strong> dengan ketentuan:</p>

            <ol>
                <li>
                    <strong>Objek Sewa:</strong> Kamar No. <strong>{$room->room_number}</strong>
                    di {$property->name}, {$property->address}.
                </li>
                <li>
                    <strong>Periode Sewa:</strong>
                    {$lease->start_date->format('d F Y')} s/d {$lease->end_date->format('d F Y')}
                </li>
                <li>
                    <strong>Harga Sewa:</strong> Rp {$this->fmt($lease->price)} per bulan
                </li>
                <li>
                    <strong>Deposit:</strong> Rp {$this->fmt($lease->deposit)}
                </li>
                <li>
                    <strong>Pembayaran:</strong> Setiap tanggal {$lease->billing_date} setiap bulan.
                    Keterlambatan lebih dari 7 hari dikenakan denda.
                </li>
                <li>
                    <strong>Larangan:</strong> Penyewa dilarang membawa tamu menginap,
                    merusak fasilitas, dan melanggar peraturan kos yang berlaku.
                </li>
                <li>
                    <strong>Pembatalan:</strong> Pemberitahuan minimal 1 bulan sebelum pindah.
                    Deposit dikembalikan setelah dikurangi biaya kerusakan (jika ada).
                </li>
            </ol>

            <p>Perjanjian ini dibuat dan ditandatangani secara digital oleh kedua belah pihak.</p>

            <div style="display:flex; justify-content:space-between; margin-top:60px;">
                <div style="text-align:center; width:45%;">
                    <p>Pemilik Kos</p>
                    <div style="height:80px; border:1px dashed #ccc; margin:10px 0;"></div>
                    <p><strong>{$appName}</strong></p>
                </div>
                <div style="text-align:center; width:45%;">
                    <p>Penyewa</p>
                    <div style="height:80px; border:1px dashed #ccc; margin:10px 0;"></div>
                    <p><strong>{$occupant->name}</strong></p>
                </div>
            </div>
        </div>
        HTML;
    }

    private function saveSignature(string $base64, string $name): string
    {
        $data    = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        $path    = "signatures/{$name}.png";
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $data);
        return $path;
    }

    private function fmt(float $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }
}
