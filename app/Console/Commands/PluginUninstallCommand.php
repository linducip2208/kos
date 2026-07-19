<?php

namespace App\Console\Commands;

use App\Core\Plugin\PluginManager;
use Illuminate\Console\Command;

class PluginUninstallCommand extends Command
{
    protected $signature   = 'plugin:uninstall {slug} {--drop-migrations : Rollback migration plugin}';
    protected $description = 'Uninstall plugin';

    public function handle(PluginManager $manager): int
    {
        $slug = $this->argument('slug');

        if (!$this->confirm("Yakin ingin uninstall plugin '{$slug}'?")) {
            $this->line('Dibatalkan.');
            return Command::SUCCESS;
        }

        $result = $manager->uninstall($slug, $this->option('drop-migrations'));

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
