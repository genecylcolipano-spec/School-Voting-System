<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;
use App\Services\Admin\AdminScopeService;

class ElectionPolicy
{
    public function __construct(protected AdminScopeService $scope) {}

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, Election $election): bool
    {
        return $this->canAccess($user, $election);
    }

    public function create(User $user): bool
    {
        return $this->canModifyElections($user);
    }

    public function update(User $user, Election $election): bool
    {
        if (! $this->canModifyElections($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->canAccess($user, $election);
    }

    public function delete(User $user, Election $election): bool
    {
        // Super Admin may delete any election (including locked/archived/closed).
        if ($user->isSuperAdmin()) {
            return $user->hasPermission('delete_data');
        }

        // Regular admins cannot remove locked results.
        if ($election->results_locked) {
            return false;
        }

        return $this->canModifyElections($user)
            && (int) $election->created_by === (int) $user->id;
    }

    protected function canAccess(User $user, Election $election): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($election->created_by === $user->id) {
            return true;
        }

        return $this->scope->assignment($user)?->election_id === $election->id;
    }

    protected function canModifyElections(User $user): bool
    {
        return ! $this->scope->isReadOnly($user)
            && ! $this->scope->isAuditor($user)
            && $user->hasPermission('modify_elections');
    }
}
