<?php

namespace App\Policies;

use App\Models\Campus;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CampusPolicy
{
    public function view(User $user, Campus $campus): Response
    {
        $allowed = $user->role === User::ROLE_CAMPUS_HEAD
            && (int) $user->campus_id === $campus->id;

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke data kampus ini.');
    }
}
