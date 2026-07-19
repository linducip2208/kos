<?php

namespace App\Http\Controllers\Api\Admin;

use App\Core\Theme\ThemeManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(private ThemeManager $manager) {}

    public function index()
    {
        return response()->json([
            'admin'    => $this->manager->getAll('admin'),
            'user'     => $this->manager->getAll('user'),
            'frontend' => $this->manager->getAll('frontend'),
        ]);
    }

    public function active()
    {
        return response()->json([
            'admin'    => $this->manager->getActive('admin'),
            'user'     => $this->manager->getActive('user'),
            'frontend' => $this->manager->getActive('frontend'),
        ]);
    }

    public function activate(Request $request)
    {
        $request->validate([
            'area' => 'required|in:admin,user,frontend',
            'slug' => 'required|string',
        ]);

        $result = $this->manager->activate($request->area, $request->slug);
        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
