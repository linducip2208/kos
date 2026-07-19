<?php

namespace Plugins\Finance\Hooks;

use Illuminate\Support\Facades\Log;

class InstallHook
{
    public function handle(): void
    {
        setting_set('finance.enabled', true, 'plugins', 'boolean');
        Log::info('Plugin finance: installed successfully.');
    }
}
