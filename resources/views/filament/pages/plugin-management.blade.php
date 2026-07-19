<x-filament-panels::page>

    <div class="space-y-4">
        @php $plugins = $this->getPlugins(); @endphp

        @if(empty($plugins))
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-10 text-center text-gray-500">
                <p class="text-lg font-medium mb-1">Belum ada plugin</p>
                <p class="text-sm">Letakkan plugin di folder <code class="bg-gray-100 px-1 rounded">plugins/</code> kemudian refresh halaman ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($plugins as $plugin)
                    <div class="bg-white border rounded-xl p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-800">{{ $plugin['name'] }}</h3>
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">v{{ $plugin['version'] }}</span>
                                    @if($plugin['is_active'])
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-semibold">Aktif</span>
                                    @elseif($plugin['is_installed'])
                                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">Nonaktif</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-400 px-2 py-0.5 rounded">Belum diinstall</span>
                                    @endif
                                    @if($plugin['license_required'])
                                        <span class="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded">🔑 License</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $plugin['description'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">by {{ $plugin['author'] }}</p>
                                @if($plugin['installed_at'])
                                    <p class="text-xs text-gray-400">Diinstall: {{ \Carbon\Carbon::parse($plugin['installed_at'])->format('d M Y') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4 flex-wrap">
                            @if(!$plugin['is_installed'])
                                <button wire:click="install('{{ $plugin['slug'] }}')"
                                        wire:confirm="Install plugin {{ $plugin['name'] }}?"
                                        class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition font-medium">
                                    Install
                                </button>
                            @elseif($plugin['is_active'])
                                <button wire:click="deactivate('{{ $plugin['slug'] }}')"
                                        wire:confirm="Nonaktifkan plugin {{ $plugin['name'] }}?"
                                        class="text-xs bg-yellow-500 text-white px-3 py-1.5 rounded-lg hover:bg-yellow-600 transition font-medium">
                                    Nonaktifkan
                                </button>
                            @else
                                <button wire:click="activate('{{ $plugin['slug'] }}')"
                                        class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition font-medium">
                                    Aktifkan
                                </button>
                                <button wire:click="uninstall('{{ $plugin['slug'] }}')"
                                        wire:confirm="Uninstall plugin {{ $plugin['name'] }}? Data plugin akan dihapus."
                                        class="text-xs bg-red-500 text-white px-3 py-1.5 rounded-lg hover:bg-red-600 transition font-medium">
                                    Uninstall
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</x-filament-panels::page>
