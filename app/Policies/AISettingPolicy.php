<?php

namespace App\Policies;

use App\Models\AISetting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AISettingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AISetting  $aiSetting
     * @return bool
     */
    public function view(User $user, AISetting $aiSetting): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AISetting  $aiSetting
     * @return bool
     */
    public function update(User $user, AISetting $aiSetting): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AISetting  $aiSetting
     * @return bool
     */
    public function delete(User $user, AISetting $aiSetting): bool
    {
        return $user->hasRole('admin');
    }
}