<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private ?string $status = null,
        private ?string $from   = null,
        private ?string $to     = null,
    ) {}

    public function query()
    {
        return Invoice::with(['lease.occupant', 'lease.room.property'])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->from,   fn ($q) => $q->where('due_date', '>=', $this->from))
            ->when($this->to,     fn ($q) => $q->where('due_date', '<=', $this->to))
            ->orderBy('due_date');
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Penyewa', 'Properti', 'Kamar', 'Periode', 'Jatuh Tempo', 'Total', 'Status', 'Dibayar'];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            $invoice->lease->occupant->name ?? '-',
            $invoice->lease->room->property->name ?? '-',
            $invoice->lease->room->room_number ?? '-',
            $invoice->period_start . ' s/d ' . $invoice->period_end,
            $invoice->due_date,
            $invoice->total,
            strtoupper($invoice->status),
            $invoice->paid_at ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
