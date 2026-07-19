<?php

namespace App\Console\Commands;

use App\Core\Plugin\PluginManager;
use Illuminate\Console\Command;

class PluginInstallCommand extends Command
{
    protected $signature   = 'plugin:install {slug} {activation_key? : Activation key untuk plugin berbayar}';
    protected $description = 'Install plugin';

    public function handle(PluginManager $manager): int
    {
        $slug   = $this->argument('slug');
        $key    = $this->argument('activation_key');
        $result = $manager->install($slug, $key);

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
