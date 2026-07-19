<?php

namespace App\Exports;

use App\Models\Lease;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OccupancyExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?string $from = null,
        private ?string $to   = null,
    ) {}

    public function query()
    {
        return Lease::with(['occupant', 'room.property'])
            ->when($this->from, fn ($q) => $q->where('start_date', '>=', $this->from))
            ->when($this->to,   fn ($q) => $q->where('start_date', '<=', $this->to))
            ->orderBy('start_date');
    }

    public function headings(): array
    {
        return ['No. Kontrak', 'Penyewa', 'Properti', 'Kamar', 'Mulai', 'Berakhir', 'Harga/Bulan', 'Siklus', 'Status'];
    }

    public function map($lease): array
    {
        return [
            $lease->lease_number,
            $lease->occupant->name ?? '-',
            $lease->room->property->name ?? '-',
            $lease->room->room_number ?? '-',
            $lease->start_date,
            $lease->end_date,
            $lease->price,
            $lease->billing_cycle,
            strtoupper($lease->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
