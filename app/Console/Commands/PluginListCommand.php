<?php

namespace App\Console\Commands;

use App\Core\Plugin\PluginManager;
use Illuminate\Console\Command;

class PluginListCommand extends Command
{
    protected $signature   = 'plugin:list';
    protected $description = 'List semua plugin yang tersedia dan status instalasinya';

    public function handle(PluginManager $manager): int
    {
        $plugins = $manager->getAll();

        if (empty($plugins)) {
            $this->info('Tidak ada plugin yang ditemukan di folder plugins/.');
            return Command::SUCCESS;
        }

        $rows = array_map(fn ($p) => [
            $p['slug'],
            $p['name'],
            $p['version'],
            $p['installed'] ? 'Ya' : 'Tidak',
            $p['active'] ? '<info>Aktif</info>' : '<comment>Nonaktif</comment>',
        ], $plugins);

        $this->table(['Slug', 'Nama', 'Versi', 'Terinstall', 'Status'], $rows);

        return Command::SUCCESS;
    }
}
