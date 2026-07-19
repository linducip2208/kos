<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PluginAccess
{
    public function handle(Request $request, Closure $next, string $pluginSlug)
    {
        if (!app('plugin.manager')->isActive($pluginSlug)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => "Plugin '{$pluginSlug}' tidak aktif."], 403);
            }

            abort(403, "Plugin '{$pluginSlug}' tidak aktif.");
        }

        return $next($request);
    }
}
