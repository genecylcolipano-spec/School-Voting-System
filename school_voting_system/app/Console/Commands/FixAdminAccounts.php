<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Console\Command;

class FixAdminAccounts extends Command
{
    protected $signature = 'portal:fix-admin-accounts
                            {--dry-run : Show what would change without writing}';

    protected $description = 'Verify and repair ADMIN-001 / SUPER-001 account_id, roles, and passkey bindings';

    /** @var array<string, array{name: string, email: string, role: UserRole}> */
    protected array $adminAccounts = [
        'ADMIN-001' => [
            'name' => 'System Administrator',
            'email' => 'admin@school.local',
            'role' => UserRole::Admin,
        ],
        'SUPER-001' => [
            'name' => 'Chief Super Admin',
            'email' => 'super@school.local',
            'role' => UserRole::SuperAdmin,
        ],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('=== Portal Admin Account Verification ===');
        $this->newLine();

        $this->line('SQL verification query:');
        $this->line("  SELECT u.id, u.account_id, u.role, u.email,");
        $this->line("         COUNT(p.id) AS passkey_count,");
        $this->line("         GROUP_CONCAT(p.credential_id) AS credential_ids");
        $this->line("  FROM users u");
        $this->line("  LEFT JOIN passkeys p ON p.user_id = u.id");
        $this->line("  WHERE u.account_id IN ('ADMIN-001', 'SUPER-001')");
        $this->line("  GROUP BY u.id, u.account_id, u.role, u.email;");
        $this->newLine();

        foreach ($this->adminAccounts as $accountId => $expected) {
            $this->info("Checking {$accountId}...");

            $user = User::query()->where('account_id', $accountId)->first();

            if (! $user) {
                $this->warn("  NOT FOUND — account_id '{$accountId}' missing from database.");

                if (! $dryRun) {
                    $user = User::query()->create([
                        'account_id' => $accountId,
                        'name' => $expected['name'],
                        'email' => $expected['email'],
                        'password' => null,
                        'role' => $expected['role'],
                    ]);
                    $this->info("  CREATED user #{$user->id} with account_id '{$accountId}'.");
                }

                continue;
            }

            $this->line("  Found user #{$user->id}");
            $this->line("  account_id: '{$user->account_id}' (bytes: ".bin2hex($user->account_id).')');
            $this->line("  role:       ".($user->role?->value ?? 'NULL'));
            $this->line("  email:      {$user->email}");

            $fixes = [];

            if ($user->account_id !== $accountId) {
                $fixes['account_id'] = $accountId;
            }

            if ($user->role !== $expected['role']) {
                $fixes['role'] = $expected['role'];
            }

            if ($user->email !== $expected['email']) {
                $fixes['email'] = $expected['email'];
            }

            if ($user->name !== $expected['name']) {
                $fixes['name'] = $expected['name'];
            }

            if (! empty($fixes)) {
                if ($dryRun) {
                    $this->warn('  Would fix: '.json_encode(array_map(
                        fn ($v) => $v instanceof UserRole ? $v->value : $v,
                        $fixes
                    )));
                } else {
                    $user->forceFill($fixes)->save();
                    $this->info('  FIXED: '.json_encode(array_map(
                        fn ($v) => $v instanceof UserRole ? $v->value : $v,
                        $fixes
                    )));
                }
            } else {
                $this->info('  Account fields OK.');
            }

            $passkeyCount = Passkey::query()->where('user_id', $user->id)->count();
            $this->line("  passkeys:   {$passkeyCount}");

            if ($passkeyCount === 0) {
                $this->warn("  No passkey registered — run: php artisan portal:enrollment-link {$accountId}");
            } else {
                Passkey::query()
                    ->where('user_id', $user->id)
                    ->get(['id', 'credential_id', 'device_name'])
                    ->each(function (Passkey $passkey) {
                        $this->line("    passkey #{$passkey->id}: {$passkey->credential_id} ({$passkey->device_name})");
                    });
            }

            $this->newLine();
        }

        $this->info('Tinker one-liner to verify exact match:');
        $this->line("  User::where('account_id', 'ADMIN-001')->first(['id','account_id','role']);");
        $this->line("  User::where('account_id', 'SUPER-001')->first(['id','account_id','role']);");
        $this->newLine();

        $this->info('Tinker one-liner to fix roles manually:');
        $this->line("  User::where('account_id','ADMIN-001')->update(['role'=>'admin']);");
        $this->line("  User::where('account_id','SUPER-001')->update(['role'=>'super_admin']);");
        $this->newLine();

        if ($dryRun) {
            $this->comment('Dry run complete — no changes written.');
        } else {
            $this->info('Admin account check complete.');
        }

        return self::SUCCESS;
    }
}
