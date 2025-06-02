<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkableSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkableSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, WorkableSetting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, WorkableSetting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, WorkableSetting $setting): bool
    {
        return $user->hasRole('admin');
    }
}
