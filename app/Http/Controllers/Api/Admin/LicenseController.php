<?php

namespace App\Http\Controllers\Api\Admin;

use App\Core\License\LicenseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LicenseController extends Controller
{
    public function __construct(private LicenseService $license) {}

    public function info()
    {
        return response()->json($this->license->info());
    }

    public function activate(Request $request)
    {
        $request->validate(['activation_key' => 'required|string']);
        $result = $this->license->activate($request->activation_key);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function validate()
    {
        Cache::forget('license_valid');
        $result = $this->license->validate();
        return response()->json($result, ($result['valid'] ?? false) ? 200 : 402);
    }

    public function revoke(Request $request)
    {
        $request->validate(['activation_key' => 'required|string']);
        $result = $this->license->revoke($request->activation_key);
        return response()->json($result);
    }
}
