<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkableJob;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkableJobPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
