<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Auth\PasskeyEnrollmentLinkService;
use Illuminate\Console\Command;

class IssuePasskeyEnrollmentLink extends Command
{
    protected $signature = 'portal:enrollment-link {account_id : The portal account ID (e.g. ADMIN-001)}';

    protected $description = 'Generate a passkey enrollment link for a portal account';

    public function handle(PasskeyEnrollmentLinkService $enrollmentLinks): int
    {
        $accountId = $this->argument('account_id');

        $user = User::query()->where('account_id', $accountId)->first();

        if (! $user) {
            $this->error("No user found with account_id '{$accountId}'.");

            return self::FAILURE;
        }

        $issued = $enrollmentLinks->issueForUser($user);

        $this->info("Enrollment link for {$user->name} ({$user->account_id}, role: {$user->role?->value}):");
        $this->line($issued['url']);

        return self::SUCCESS;
    }
}
