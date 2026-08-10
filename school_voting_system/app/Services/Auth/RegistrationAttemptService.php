<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class RegistrationAttemptService
{
    public function assertNotBlocked(Request $request, string $accountId): void
    {
        if (RateLimiter::tooManyAttempts($this->key($request, $accountId), $this->maxAttempts())) {
            throw ValidationException::withMessages([
                'account_id' => ['Too many unsuccessful registration attempts. Please contact your school administrator.'],
            ]);
        }
    }

    public function recordFailure(Request $request, string $accountId): void
    {
        RateLimiter::hit(
            $this->key($request, $accountId),
            $this->decaySeconds(),
        );
    }

    public function clear(Request $request, string $accountId): void
    {
        RateLimiter::clear($this->key($request, $accountId));
    }

    public function remainingAttempts(Request $request, string $accountId): int
    {
        return RateLimiter::remaining($this->key($request, $accountId), $this->maxAttempts());
    }

    protected function key(Request $request, string $accountId): string
    {
        $normalized = strtolower(preg_replace('/\s+/', '', trim($accountId)) ?? '');

        return 'registration-validate:'.sha1($request->ip().'|'.$normalized);
    }

    protected function maxAttempts(): int
    {
        return max(1, (int) config('enrollment.max_validation_attempts', 3));
    }

    protected function decaySeconds(): int
    {
        return max(60, (int) config('enrollment.validation_attempt_decay_minutes', 60) * 60);
    }
}
