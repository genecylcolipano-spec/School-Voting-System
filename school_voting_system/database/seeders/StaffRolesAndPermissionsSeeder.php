<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\StaffRole;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['key' => 'delete_data', 'label' => 'Delete Records', 'category' => 'data'],
            ['key' => 'modify_elections', 'label' => 'Modify Elections', 'category' => 'elections'],
            ['key' => 'pause_election', 'label' => 'Pause / Resume Voting', 'category' => 'elections'],
            ['key' => 'approve_posters', 'label' => 'Approve / Reject Posters', 'category' => 'elections'],
            ['key' => 'verify_candidates', 'label' => 'Verify Candidates', 'category' => 'elections'],
            ['key' => 'send_reminders', 'label' => 'Send Voting Reminders', 'category' => 'users'],
            ['key' => 'approve_talent_entries', 'label' => 'Approve Talent Event Entries', 'category' => 'events'],
            ['key' => 'manage_talent_voting', 'label' => 'Manage Talent Event Voting', 'category' => 'events'],
            ['key' => 'create_talent_events', 'label' => 'Create Talent Competitions', 'category' => 'events'],
            ['key' => 'publish_talent_results', 'label' => 'Publish Talent Event Results', 'category' => 'events'],
            ['key' => 'publish_election_results', 'label' => 'Publish Election Results', 'category' => 'elections'],
            ['key' => 'view_audit_logs', 'label' => 'View Audit Logs', 'category' => 'security'],
            ['key' => 'manage_passkeys', 'label' => 'Manage Passkeys', 'category' => 'security'],
            ['key' => 'manage_backups', 'label' => 'Manage Backups', 'category' => 'system'],
            ['key' => 'export_reports', 'label' => 'Export Reports', 'category' => 'reports'],
            ['key' => 'manage_students', 'label' => 'Manage Students', 'category' => 'users'],
            ['key' => 'read_only', 'label' => 'Read-Only Access', 'category' => 'general'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(['key' => $permission['key']], $permission);
        }

        $allPermissionIds = Permission::query()->pluck('id');

        $roles = [
            'chief_super_admin' => [
                'name' => 'Chief Super Admin',
                'description' => 'Full unrestricted system control.',
                'permissions' => $allPermissionIds,
            ],
            'election_admin' => [
                'name' => 'Operations Admin',
                'description' => 'Manage assigned elections, campus activities, posters, candidates, and scoped results.',
                'permissions' => Permission::query()->whereIn('key', [
                    'modify_elections',
                    'manage_passkeys',
                    'pause_election',
                    'approve_posters',
                    'verify_candidates',
                    'send_reminders',
                    'approve_talent_entries',
                    'create_talent_events',
                    'manage_talent_voting',
                    'publish_talent_results',
                    'publish_election_results',
                    'export_reports',
                    'view_audit_logs',
                ])->pluck('id'),
            ],
            'student_records_admin' => [
                'name' => 'Student Records Admin',
                'description' => 'Verify candidates and manage voter eligibility in assigned scope.',
                'permissions' => Permission::query()->whereIn('key', [
                    'verify_candidates',
                    'send_reminders',
                    'manage_students',
                    'export_reports',
                ])->pluck('id'),
            ],
            'auditor' => [
                'name' => 'Auditor',
                'description' => 'Read-only cross-checks and own activity log in assigned scope.',
                'permissions' => Permission::query()->whereIn('key', [
                    'view_audit_logs',
                    'export_reports',
                    'read_only',
                ])->pluck('id'),
            ],
            'read_only_admin' => [
                'name' => 'Read-Only Admin',
                'description' => 'View dashboards without modification rights.',
                'permissions' => Permission::query()->whereIn('key', ['read_only', 'export_reports'])->pluck('id'),
            ],
        ];

        foreach ($roles as $slug => $roleData) {
            $role = StaffRole::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $roleData['name'], 'description' => $roleData['description'], 'is_system' => true],
            );

            $role->permissions()->sync($roleData['permissions']);
        }

        $chiefRole = StaffRole::query()->where('slug', 'chief_super_admin')->first();

        if ($chiefRole) {
            User::query()->where('account_id', 'SUPER-001')->update(['staff_role_id' => $chiefRole->id]);
            User::query()->where('account_id', 'ADMIN-001')->update([
                'staff_role_id' => StaffRole::query()->where('slug', 'election_admin')->value('id'),
            ]);
        }

        SystemSetting::setValue('session_timeout_minutes', 30, 'integer');
        SystemSetting::setValue('ip_whitelist_enabled', false, 'boolean');
        SystemSetting::setValue('ip_whitelist', ['127.0.0.1', '::1'], 'json');
        SystemSetting::setValue('two_factor_recovery_enabled', true, 'boolean');
        SystemSetting::setValue('public_results_published', false, 'boolean');
        SystemSetting::setValue('support_email', 'ictsupport@school.edu');
        SystemSetting::setValue('support_team_label', 'ICT Support Team');
    }
}
