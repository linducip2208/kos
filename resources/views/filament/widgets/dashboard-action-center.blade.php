<x-filament-widgets::widget class="km-action-center">
    @php($meta = [
        'danger' => [
            'icon' => 'heroicon-m-exclamation-triangle',
            'label' => 'Butuh tindakan segera',
        ],
        'warning' => [
            'icon' => 'heroicon-m-clock',
            'label' => 'Perlu perhatian',
        ],
        'info' => [
            'icon' => 'heroicon-m-inbox',
            'label' => 'Menunggu respons',
        ],
    ])
    <x-filament::section>
        <x-slot name="heading">
            Pusat Tindakan
        </x-slot>
        <x-slot name="description">
            Prioritas operasional yang membutuhkan perhatian Anda.
        </x-slot>

        @if (count($actions) > 0)
            <div class="km-action-grid">
                @foreach ($actions as $action)
                    @php($tone = $meta[$action['tone']] ?? $meta['info'])
                    <a href="{{ $action['url'] }}" class="km-action-card km-action-card--{{ $action['tone'] }}">
                        <span class="km-action-icon">
                            <x-filament::icon :icon="$tone['icon']" class="km-action-svg" />
                        </span>
                        <span class="km-action-body">
                            <span class="km-action-label">{{ $action['label'] }}</span>
                            <span class="km-action-hint">{{ $tone['label'] }} &rsaquo;</span>
                        </span>
                        <span class="km-action-count">{{ $action['count'] }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="km-empty">
                <span class="km-empty-icon">
                    <x-filament::icon icon="heroicon-m-check-badge" class="km-action-svg" />
                </span>
                <p class="km-empty-title">Semua prioritas sudah tertangani</p>
                <p class="km-empty-text">Tidak ada invoice terlambat, kontrak mendesak, maintenance melewati SLA, atau booking yang menunggu respons.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
