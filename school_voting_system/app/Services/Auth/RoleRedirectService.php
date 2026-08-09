<?php

namespace App\Services\Auth;

use App\Enums\UserRole;
use App\Models\User;

class RoleRedirectService
{
    public function dashboardPathFor(User $user): string
    {
        return $user->role?->dashboardPath() ?? UserRole::Student->dashboardPath();
    }

    public function routeNameFor(User $user): string
    {
        return match ($user->role) {
            UserRole::SuperAdmin => 'super-admin.dashboard',
            UserRole::Admin => 'admin.dashboard',
            UserRole::Faculty => 'faculty.dashboard',
            default => 'student.dashboard',
        };
    }
}
