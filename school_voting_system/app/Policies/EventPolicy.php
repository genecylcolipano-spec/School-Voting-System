<?php

namespace App\Policies;

use App\Models\User;

class EventPolicy extends PortalContentPolicy
{
    public function update(User $user, mixed $model): bool
    {
        return $this->delete($user, $model);
    }
}
