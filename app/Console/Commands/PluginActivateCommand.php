<?php

namespace App\Console\Commands;

use App\Core\Plugin\PluginManager;
use Illuminate\Console\Command;

class PluginActivateCommand extends Command
{
    protected $signature   = 'plugin:activate {slug}';
    protected $description = 'Aktifkan plugin yang sudah diinstall';

    public function handle(PluginManager $manager): int
    {
        $result = $manager->activate($this->argument('slug'));

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
