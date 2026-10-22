<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ROLE_CAMPUS_HEAD && $user->assignedCampusId() !== null;
    }

    public function view(User $user, Unit $unit): Response
    {
        $allowed = ($user->role === User::ROLE_CAMPUS_HEAD
                && $user->assignedCampusId() === (int) $unit->campus_id)
            || (in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
                && (int) $user->unit_id === $unit->id);

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses ke data unit ini.');
    }

    public function manage(User $user, Unit $unit): Response
    {
        $allowed = $user->role === User::ROLE_CAMPUS_HEAD
            && $user->assignedCampusId() === (int) $unit->campus_id;

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin untuk mengelola unit ini.');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }
}
