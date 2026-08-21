<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Services\Admin\AdminScopeService;

class AnnouncementPolicy
{
    public function __construct(protected AdminScopeService $scope) {}

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function viewAll(User $user): bool
    {
        return $user->isSuperAdmin()
            || $this->scope->isAuditor($user)
            || $this->scope->isReadOnly($user);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($this->viewAll($user)) {
            return true;
        }

        return (int) $announcement->created_by === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if (! $this->canManage($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $announcement->created_by === (int) $user->id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }

    protected function canManage(User $user): bool
    {
        return ($user->isAdmin() || $user->isSuperAdmin())
            && ! $this->scope->isReadOnly($user)
            && ! $this->scope->isAuditor($user);
    }
}
