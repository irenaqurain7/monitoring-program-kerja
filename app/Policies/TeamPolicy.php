<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    public function manage(User $user, Team $team): Response
    {
        $allowed = $user->role === User::ROLE_CAMPUS_HEAD
            && $user->assignedCampusId() !== null
            && (int) $team->unit?->campus_id === $user->assignedCampusId();

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk mengelola tim ini.');
    }
}
