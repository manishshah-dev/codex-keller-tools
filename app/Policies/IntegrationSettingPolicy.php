<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IntegrationSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class IntegrationSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, IntegrationSetting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, IntegrationSetting $setting): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, IntegrationSetting $setting): bool
    {
        return $user->hasRole('admin');
    }
}
