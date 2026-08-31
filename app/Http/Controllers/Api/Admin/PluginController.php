<?php

namespace App\Http\Controllers\Api\Admin;

use App\Core\Plugin\PluginManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PluginController extends Controller
{
    public function __construct(private PluginManager $manager) {}

    public function index()
    {
        Gate::authorize('plugin.manage');

        return response()->json($this->manager->getAll());
    }

    public function install(Request $request)
    {
        Gate::authorize('plugin.manage');
        $request->validate([
            'slug' => 'required|string',
            'activation_key' => 'nullable|string',
        ]);

        $result = $this->manager->install($request->slug, $request->activation_key);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function activate(string $slug)
    {
        Gate::authorize('plugin.manage');
        $result = $this->manager->activate($slug);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function deactivate(string $slug)
    {
        Gate::authorize('plugin.manage');
        $result = $this->manager->deactivate($slug);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function destroy(string $slug)
    {
        Gate::authorize('plugin.manage');
        $result = $this->manager->uninstall($slug);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
