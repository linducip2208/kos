<x-filament-panels::page>

    @php
        $allThemes = $this->getThemes();
        $areas = [
            'admin'    => 'Admin Panel',
            'user'     => 'Portal Penyewa',
            'frontend' => 'Landing Page (Publik)',
        ];
    @endphp

    <div x-data="{ tab: 'admin' }" class="space-y-4">

        {{-- Tab buttons --}}
        <div class="flex gap-1 border-b border-gray-200">
            @foreach($areas as $area => $areaLabel)
                <button type="button"
                        x-on:click="tab = '{{ $area }}'"
                        :class="tab === '{{ $area }}'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition focus:outline-none">
                    {{ $areaLabel }}
                    @if(!empty($allThemes[$area]))
                        <span class="ml-1.5 text-xs text-gray-400">({{ count($allThemes[$area]) }})</span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Tab panels --}}
        @foreach($areas as $area => $areaLabel)
            <div x-show="tab === '{{ $area }}'" x-cloak>
                @if(empty($allThemes[$area]))
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center text-gray-400 text-sm">
                        Belum ada theme di folder <code>themes/{{ $area }}/</code>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($allThemes[$area] as $theme)
                            <div class="bg-white border-2 {{ $theme['active'] ? 'border-blue-500' : 'border-gray-100' }} rounded-xl p-5 shadow-sm">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold text-gray-800">{{ $theme['name'] ?? $theme['slug'] }}</h3>
                                        <p class="text-xs text-gray-400 mt-0.5">v{{ $theme['version'] ?? '1.0.0' }}</p>
                                        @if(!empty($theme['description']))
                                            <p class="text-sm text-gray-500 mt-1">{{ $theme['description'] }}</p>
                                        @endif
                                    </div>
                                    @if($theme['active'])
                                        <span class="flex-shrink-0 bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded-lg">Aktif</span>
                                    @endif
                                </div>

                                @if(!$theme['active'])
                                    <button wire:click="activate('{{ $area }}', '{{ $theme['slug'] }}')"
                                            wire:confirm="Aktifkan theme {{ $theme['name'] ?? $theme['slug'] }} untuk {{ $areaLabel }}?"
                                            class="mt-4 w-full text-xs bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                                        Aktifkan Theme Ini
                                    </button>
                                @else
                                    <div class="mt-4 w-full text-center text-xs text-blue-600 font-medium py-2">
                                        ✓ Sedang Digunakan
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

    </div>

</x-filament-panels::page>
