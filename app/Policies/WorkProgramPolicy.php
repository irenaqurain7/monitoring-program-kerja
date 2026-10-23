<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Auth\Access\Response;

class WorkProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            User::ROLE_CAMPUS_HEAD,
            User::ROLE_UNIT_HEAD,
            User::ROLE_UNIT_MEMBER,
        ], true);
    }

    public function view(User $user, WorkProgram $program): Response
    {
        if ($user->role === User::ROLE_CAMPUS_HEAD
            && $user->assignedCampusId() !== null
            && (int) $program->campus_id === $user->assignedCampusId()) {
            return Response::allow();
        }

        if (in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
            && $user->unit_id !== null
            && (int) $program->unit_id === (int) $user->unit_id) {
            return Response::allow();
        }

        return Response::deny('Anda tidak memiliki akses ke program kerja ini.');
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
            && $user->unit_id !== null
            && $user->unit?->is_active;
    }

    public function update(User $user, WorkProgram $program): bool
    {
        return in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
            && $user->unit_id !== null
            && $user->unit?->is_active
            && (int) $program->unit_id === (int) $user->unit_id;
    }

    public function changeStatus(User $user, WorkProgram $program): bool
    {
        if ($this->update($user, $program)) {
            return true;
        }

        return $user->role === User::ROLE_CAMPUS_HEAD
            && $user->assignedCampusId() !== null
            && (int) $program->campus_id === (int) $user->assignedCampusId();
    }

    public function reportProgress(User $user, WorkProgram $program): bool
    {
        return in_array($user->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
            && $user->unit_id !== null
            && (int) $program->unit_id === (int) $user->unit_id
            && $program->status !== WorkProgram::STATUS_COMPLETED;
    }
}
