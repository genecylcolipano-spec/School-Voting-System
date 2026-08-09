<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class IssuePasskeyEnrollmentLink extends Command
{
    protected $signature = 'portal:enrollment-link {account_id : The portal account ID (e.g. ADMIN-001)}';

    protected $description = 'Generate a signed passkey enrollment link for a portal account';

    public function handle(): int
    {
        $accountId = $this->argument('account_id');

        $user = User::query()->where('account_id', $accountId)->first();

        if (! $user) {
            $this->error("No user found with account_id '{$accountId}'.");

            return self::FAILURE;
        }

        $url = URL::temporarySignedRoute(
            'register.passkey.bootstrap',
            now()->addHours(2),
            ['user' => $user->id]
        );

        $this->info("Enrollment link for {$user->name} ({$user->account_id}, role: {$user->role?->value}):");
        $this->line($url);

        return self::SUCCESS;
    }
}
