<?php

namespace Plugins\Finance\Hooks;

use Illuminate\Support\Facades\Log;

class UninstallHook
{
    public function handle(): void
    {
        Log::info('Plugin finance: uninstalled.');
    }
}
