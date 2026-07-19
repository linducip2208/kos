<?php

namespace App\Console\Commands;

use App\Core\Theme\ThemeManager;
use Illuminate\Console\Command;

class ThemeActivateCommand extends Command
{
    protected $signature   = 'theme:activate {area} {slug}';
    protected $description = 'Aktifkan theme untuk area tertentu (admin/user/frontend)';

    public function handle(ThemeManager $manager): int
    {
        $result = $manager->activate($this->argument('area'), $this->argument('slug'));

        if ($result['success']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
