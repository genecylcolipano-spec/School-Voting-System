<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case Faculty = 'faculty';

    public function canVote(): bool
    {
        return $this === self::Student;
    }

    public function canDonate(): bool
    {
        return $this === self::Student;
    }

    public function dashboardPath(): string
    {
        return match ($this) {
            self::SuperAdmin => '/super-admin/dashboard',
            self::Admin => '/admin/dashboard',
            self::Faculty => '/faculty/dashboard',
            self::Student => '/student/dashboard',
        };
    }

    /**
     * Resolve a route middleware parameter to a UserRole (e.g. "super_admin" → SuperAdmin).
     */
    public static function fromRouteParam(string $role): self
    {
        $normalized = str_replace('-', '_', strtolower(trim($role)));

        return match ($normalized) {
            'admin' => self::Admin,
            'super_admin', 'superadmin' => self::SuperAdmin,
            'faculty' => self::Faculty,
            default => self::from($normalized),
        };
    }
}
