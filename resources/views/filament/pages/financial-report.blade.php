<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $revenueByMonth = $this->getRevenueByMonth();
        $occupancyByProperty = $this->getOccupancyByProperty();
        $overdue = $this->getOverdueSummary();
    @endphp

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Bulan Ini</div>
            <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['thisMonth'], 0, ',', '.') }}</div>
            @if($stats['lastMonth'] > 0)
                <div class="text-xs text-gray-400 mt-1">
                    Bulan lalu: Rp {{ number_format($stats['lastMonth'], 0, ',', '.') }}
                </div>
            @endif
        </x-filament::card>
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Tahun Ini (YTD)</div>
            <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['ytd'], 0, ',', '.') }}</div>
        </x-filament::card>
        <x-filament::card>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Tunggakan</div>
            <div class="text-2xl font-bold text-red-600">Rp {{ number_format($overdue['amount'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $overdue['count'] }} invoice belum dibayar</div>
        </x-filament::card>
    </div>

    {{-- Revenue per Month Table --}}
    <x-filament::card class="mb-6">
        <h3 class="text-lg font-semibold mb-4">Pendapatan 12 Bulan Terakhir</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-2 px-3">Bulan</th>
                        <th class="text-right py-2 px-3">Pendapatan</th>
                        <th class="text-left py-2 px-3">Grafik</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxRevenue = collect($revenueByMonth)->max('total') ?: 1; @endphp
                    @foreach($revenueByMonth as $row)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="py-2 px-3">{{ $row['month'] }}</td>
                        <td class="text-right py-2 px-3 font-medium">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                        <td class="py-2 px-3">
                            <div class="h-3 rounded bg-green-500" style="width: {{ $maxRevenue > 0 ? round(($row['total']/$maxRevenue)*100) : 0 }}%; min-width: {{ $row['total'] > 0 ? '4px' : '0' }}"></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::card>

    {{-- Occupancy by Property --}}
    <x-filament::card>
        <h3 class="text-lg font-semibold mb-4">Tingkat Hunian per Properti</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-2 px-3">Properti</th>
                        <th class="text-center py-2 px-3">Total Kamar</th>
                        <th class="text-center py-2 px-3">Terisi</th>
                        <th class="text-center py-2 px-3">Rate</th>
                        <th class="text-left py-2 px-3">Grafik</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($occupancyByProperty as $prop)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="py-2 px-3 font-medium">{{ $prop['name'] }}</td>
                        <td class="text-center py-2 px-3">{{ $prop['total'] }}</td>
                        <td class="text-center py-2 px-3">{{ $prop['occupied'] }}</td>
                        <td class="text-center py-2 px-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $prop['rate'] >= 80 ? 'bg-green-100 text-green-800' : ($prop['rate'] >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $prop['rate'] }}%
                            </span>
                        </td>
                        <td class="py-2 px-3">
                            <div class="h-3 rounded {{ $prop['rate'] >= 80 ? 'bg-green-500' : ($prop['rate'] >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}" style="width: {{ $prop['rate'] }}%; min-width: {{ $prop['rate'] > 0 ? '4px' : '0' }}"></div>
                        </td>
                    </tr>
                    @endforeach
                    @if(empty($occupancyByProperty))
                    <tr><td colspan="5" class="text-center py-4 text-gray-400">Belum ada data properti</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-filament::card>
</x-filament-panels::page>
