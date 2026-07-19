<?php

namespace App\Console\Commands;

use App\Core\Plugin\PluginManager;
use Illuminate\Console\Command;

class PluginDeactivateCommand extends Command
{
    protected $signature   = 'plugin:deactivate {slug}';
    protected $description = 'Nonaktifkan plugin';

    public function handle(PluginManager $manager): int
    {
        $result = $manager->deactivate($this->argument('slug'));

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
