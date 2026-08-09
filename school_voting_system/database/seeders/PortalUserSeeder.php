<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class PortalUserSeeder extends Seeder
{
    /**
     * Creates portal users without passwords (passkey-only authentication).
     * Register a passkey from the dashboard after first signed session, or use an enrollment link.
     */
    public function run(): void
    {
        $this->seedUser('SUPER-001', 'Chief Super Admin', 'super@school.local', UserRole::SuperAdmin);
        $this->seedUser('ADMIN-001', 'System Administrator', 'admin@school.local', UserRole::Admin);
        $this->seedUser('FACULTY-001', 'Faculty Member', 'faculty@school.local', UserRole::Faculty);
        $this->seedUser('2026-00001', 'Juan Dela Cruz', '2026-00001@school.local', UserRole::Student);
    }

    protected function seedUser(string $accountId, string $name, string $email, UserRole $role): void
    {
        User::query()->updateOrCreate(
            ['account_id' => $accountId],
            [
                'name' => $name,
                'email' => $email,
                'password' => null,
                'role' => $role,
            ]
        );
    }
}
