<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Admin\AdminScopeService;

class UserPolicy
{
    public function __construct(protected AdminScopeService $scope) {}

    public function viewAnyStudents(User $user): bool
    {
        return ($user->isAdmin() || $user->isSuperAdmin())
            && ! $this->scope->isReadOnly($user);
    }

    public function issuePasskeyReset(User $actor, User $target): bool
    {
        if ($this->scope->isReadOnly($actor)) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return $actor->hasPermission('manage_passkeys');
        }

        if (! $actor->hasPermission('manage_passkeys') && ! $actor->hasPermission('manage_students')) {
            return false;
        }

        if (! $target->isStudent()) {
            return false;
        }

        return $this->scope->scopedStudentsQuery($actor)
            ->whereKey($target->id)
            ->exists();
    }

    public function updateStudentRecord(User $actor, User $target): bool
    {
        if ($this->scope->isReadOnly($actor) || $this->scope->isAuditor($actor)) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return $target->isStudent();
        }

        if (! $actor->hasPermission('manage_students')) {
            return false;
        }

        return $this->scope->canManageStudent($actor, $target);
    }

    public function importStudentRecords(User $user): bool
    {
        if ($this->scope->isReadOnly($user) || $this->scope->isAuditor($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdmin() && $user->hasPermission('manage_students');
    }
}
