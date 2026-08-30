<x-filament-widgets::widget>
    @php($toneClasses = [
        'danger' => 'bg-danger-100 text-danger-700 dark:bg-danger-500/15 dark:text-danger-300',
        'warning' => 'bg-warning-100 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300',
        'info' => 'bg-info-100 text-info-700 dark:bg-info-500/15 dark:text-info-300',
    ])
    <x-filament::section heading="Pusat Tindakan" description="Prioritas operasional yang membutuhkan perhatian.">
        @if (count($actions) > 0)
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($actions as $action)
                    <a href="{{ $action['url'] }}" class="flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $action['label'] }}</span>
                        <span class="rounded-full px-3 py-1 text-sm font-bold {{ $toneClasses[$action['tone']] ?? $toneClasses['info'] }}">{{ $action['count'] }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-dashed border-emerald-300 bg-emerald-50 p-5 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                Semua prioritas utama sudah tertangani. Tidak ada tindakan mendesak.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
