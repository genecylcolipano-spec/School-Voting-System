<?php

namespace App\Policies;

use App\Models\User;
use App\Services\Admin\AdminScopeService;
use Illuminate\Database\Eloquent\Model;

class PortalContentPolicy
{
    public function __construct(protected AdminScopeService $scope) {}

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function view(User $user, mixed $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManagePortalContent($user);
    }

    public function update(User $user, mixed $model): bool
    {
        return $this->canManagePortalContent($user);
    }

    public function delete(User $user, mixed $model): bool
    {
        if (! $this->canManagePortalContent($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $model instanceof Model
            && isset($model->created_by)
            && (int) $model->created_by === (int) $user->id;
    }

    protected function canManagePortalContent(User $user): bool
    {
        return ($user->isAdmin() || $user->isSuperAdmin())
            && ! $this->scope->isReadOnly($user)
            && ! $this->scope->isAuditor($user);
    }
}
