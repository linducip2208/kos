<?php

namespace App\Http\Middleware;

use App\Core\Tenant\TenantService;
use Closure;
use Illuminate\Http\Request;

class TenantResolver
{
    /**
     * STANDALONE: middleware ini TIDAK di-register di bootstrap/app.php
     * SAAS: aktifkan dengan menambahkan ke web/api middleware group
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
