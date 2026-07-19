<?php

namespace App\Console\Commands;

use App\Core\Theme\ThemeManager;
use Illuminate\Console\Command;

class ThemeListCommand extends Command
{
    protected $signature   = 'theme:list {area? : Area theme: admin, user, frontend}';
    protected $description = 'List semua theme yang tersedia';

    public function handle(ThemeManager $manager): int
    {
        $areas = $this->argument('area')
            ? [$this->argument('area')]
            : config('themes.areas', ['admin', 'user', 'frontend']);

        foreach ($areas as $area) {
            $this->info("\nArea: {$area}");
            $themes = $manager->getAll($area);

            if (empty($themes)) {
                $this->line('  (tidak ada theme)');
                continue;
            }

            $rows = array_map(fn ($t) => [
                $t['slug'],
                $t['name'],
                $t['version'] ?? '1.0.0',
                $t['active'] ? '<info>Aktif</info>' : '-',
            ], $themes);

            $this->table(['Slug', 'Nama', 'Versi', 'Status'], $rows);
        }

        return Command::SUCCESS;
    }
}
