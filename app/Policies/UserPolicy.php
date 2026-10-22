<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            User::ROLE_SYSTEM_ADMIN,
            User::ROLE_CAMPUS_HEAD,
            User::ROLE_UNIT_HEAD,
        ], true);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id || $this->canManageTarget($user, $target);
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $this->canManageTarget($user, $target);
    }

    public function toggleStatus(User $user, User $target): Response
    {
        if ($user->id === $target->id) {
            return Response::deny('Anda tidak dapat mengubah status akun sendiri.');
        }

        if (! $this->canManageTarget($user, $target)) {
            return Response::deny('Anda tidak memiliki izin untuk mengubah status akun ini.');
        }

        return Response::allow();
    }

    private function canManageTarget(User $user, User $target): bool
    {
        if ($user->role === User::ROLE_SYSTEM_ADMIN) {
            return true;
        }

        if ($user->role === User::ROLE_CAMPUS_HEAD) {
            $campusId = $user->assignedCampusId();

            return in_array($target->role, [User::ROLE_UNIT_HEAD, User::ROLE_UNIT_MEMBER], true)
                && $campusId !== null
                && $target->assignedCampusId() === $campusId;
        }

        return $user->role === User::ROLE_UNIT_HEAD
            && $user->unit_id !== null
            && $target->role === User::ROLE_UNIT_MEMBER
            && (int) $target->unit_id === (int) $user->unit_id;
    }
}
