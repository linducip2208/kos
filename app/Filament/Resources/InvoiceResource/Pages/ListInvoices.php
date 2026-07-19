<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Lease;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Buat Tagihan'),
            Actions\Action::make('print_report')
                ->label('Cetak Laporan PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('print.report.invoices'))
                ->openUrlInNewTab(),
            Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('print.excel.invoices'))
                ->openUrlInNewTab(),
            Actions\Action::make('generate_monthly')
                ->label('Generate Tagihan Bulan Ini')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generate tagihan bulanan otomatis?')
                ->action(function () {
                    $count = 0;
                    $today = Carbon::today();

                    Lease::where('status', 'active')
                        ->where('billing_cycle', 'monthly')
                        ->whereDay('billing_date', $today->day)
                        ->with(['room'])
                        ->each(function (Lease $lease) use (&$count, $today) {
                            $exists = Invoice::where('lease_id', $lease->id)
                                ->whereMonth('period_start', $today->month)
                                ->whereYear('period_start', $today->year)
                                ->exists();

                            if (!$exists) {
                                $prefix = setting('invoice_prefix', 'INV');
                                $num    = Invoice::whereYear('created_at', $today->year)->count() + 1;

                                Invoice::create([
                                    'lease_id'       => $lease->id,
                                    'invoice_number' => sprintf('%s-%s-%04d', $prefix, $today->format('Ym'), $num),
                                    'period_start'   => $today->copy()->startOfMonth(),
                                    'period_end'     => $today->copy()->endOfMonth(),
                                    'due_date'       => $today->copy()->addDays(7),
                                    'base_amount'    => $lease->price,
                                    'total'          => $lease->price,
                                    'status'         => 'sent',
                                    'sent_at'        => now(),
                                ]);
                                $count++;
                            }
                        });

                    Notification::make()
                        ->title("{$count} tagihan berhasil dibuat.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all'     => Tab::make('Semua'),
            'unpaid'  => Tab::make('Belum Lunas')->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', ['sent', 'overdue'])),
            'overdue' => Tab::make('Tunggakan')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'overdue')->orWhere(fn ($q) => $q->where('status', '!=', 'paid')->where('due_date', '<', now()))),
            'paid'    => Tab::make('Lunas')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'paid')),
        ];
    }
}
