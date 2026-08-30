<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy: subclass & set $viewGate / $manageGate.
 * Owner & super_admin di-bypass lewat Gate::before di AppServiceProvider.
 * Method view* → $viewGate; create/update/delete → $manageGate.
 */
abstract class BasePolicy
{
    protected string $viewGate = '';
    protected string $manageGate = '';

    public function viewAny(User $user): bool
    {
        return $this->allows($user, $this->viewGate);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, $this->viewGate);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->allows($user, $this->manageGate);
    }

    protected function allows(User $user, string $permission): bool
    {
        if ($permission === '') {
            return false;
        }

        return in_array($permission, Permissions::permissionsFor($user->role), true);
    }
}
