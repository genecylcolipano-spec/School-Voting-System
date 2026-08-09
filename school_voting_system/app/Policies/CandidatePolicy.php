<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;
use App\Services\Admin\AdminScopeService;

class CandidatePolicy
{
    public function __construct(protected AdminScopeService $scope) {}

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return $this->canAccess($user, $candidate);
    }

    public function create(User $user): bool
    {
        if (! $this->canModifyElections($user)) {
            return false;
        }

        return $user->isSuperAdmin() || $this->scope->assignment($user)?->election_id !== null;
    }

    public function update(User $user, Candidate $candidate): bool
    {
        if (! $this->canModifyElections($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->canAccess($user, $candidate);
    }

    public function delete(User $user, Candidate $candidate): bool
    {
        if ($candidate->votes()->count() > 0) {
            return false;
        }

        if (! $this->canModifyElections($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->canAccess($user, $candidate);
    }

    protected function canAccess(User $user, Candidate $candidate): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $electionId = $this->scope->assignment($user)?->election_id;

        return $electionId !== null && $candidate->election_id === $electionId;
    }

    protected function canModifyElections(User $user): bool
    {
        return ! $this->scope->isReadOnly($user)
            && ! $this->scope->isAuditor($user)
            && $user->hasPermission('modify_elections');
    }
}
