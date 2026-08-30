<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::select('id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at')->get());
    }

    public function store(Request $request)
    {
        $this->ownerOnly($request);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => ['required', Rule::in(array_keys(Permissions::ROLES))],
            'phone' => 'nullable|string|max:20',
        ]);

        if ($data['role'] === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Hanya super admin yang dapat membuat super admin.');
        }
        $user = User::create($data);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'phone']), 201);
    }

    public function update(Request $request, User $user)
    {
        $this->ownerOnly($request);

        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'phone' => 'nullable|string|max:20',
            'role' => ['sometimes', Rule::in(array_keys(Permissions::ROLES))],
            'is_active' => 'boolean',
        ]);

        if (($data['role'] ?? null) === 'super_admin' && ! $request->user()->isSuperAdmin()) {
            abort(403, 'Hanya super admin yang dapat menetapkan role super admin.');
        }
        $user->update($data);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'phone', 'is_active']));
    }

    public function destroy(Request $request, User $user)
    {
        $this->ownerOnly($request);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa hapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User dihapus.']);
    }

    private function ownerOnly(Request $request): void
    {
        if (! $request->user()->can('user.manage')) {
            abort(403, 'Anda tidak memiliki izin mengelola user.');
        }
    }
}
