<?php

namespace App\Http\Controllers\Api\Admin;

use App\Core\Theme\ThemeManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ThemeController extends Controller
{
    public function __construct(private ThemeManager $manager) {}

    public function index()
    {
        Gate::authorize('theme.manage');

        return response()->json([
            'admin' => $this->manager->getAll('admin'),
            'user' => $this->manager->getAll('user'),
            'frontend' => $this->manager->getAll('frontend'),
        ]);
    }

    public function active()
    {
        Gate::authorize('theme.manage');

        return response()->json([
            'admin' => $this->manager->getActive('admin'),
            'user' => $this->manager->getActive('user'),
            'frontend' => $this->manager->getActive('frontend'),
        ]);
    }

    public function activate(Request $request)
    {
        Gate::authorize('theme.manage');
        $request->validate([
            'area' => 'required|in:admin,user,frontend',
            'slug' => 'required|string',
        ]);

        $result = $this->manager->activate($request->area, $request->slug);

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
