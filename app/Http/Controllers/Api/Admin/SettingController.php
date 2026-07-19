<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $group    = $request->group ?? 'general';
        $settings = Setting::where('group', $group)->get(['key', 'value', 'type', 'group']);
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'         => 'required|array',
            'settings.*.key'   => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.group' => 'nullable|string',
        ]);

        foreach ($request->settings as $item) {
            $group = $item['group'] ?? 'general';
            Setting::set($item['key'], $item['value'], $group);
        }

        Cache::flush();
        return response()->json(['message' => 'Pengaturan disimpan.']);
    }

    public function byGroup(string $group)
    {
        return response()->json(Setting::where('group', $group)->get(['key', 'value', 'type']));
    }
}
