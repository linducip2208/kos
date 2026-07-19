<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('portal')->check()) {
            return redirect()->route('portal.login');
        }
        return $next($request);
    }
}
