<?php

namespace App\Models\Concerns;

use App\Enums\RosterRegistrationStatus;

trait HasRosterRegistrationStatus
{
    public function registrationStatus(): RosterRegistrationStatus
    {
        if ($this->is_registered) {
            return RosterRegistrationStatus::Registered;
        }

        $status = $this->registration_status instanceof RosterRegistrationStatus
            ? $this->registration_status
            : RosterRegistrationStatus::tryFrom((string) ($this->registration_status ?? ''))
                ?? RosterRegistrationStatus::NotRegistered;

        return $status;
    }

    public function isFullyRegistered(): bool
    {
        return $this->registrationStatus() === RosterRegistrationStatus::Registered
            || (bool) $this->is_registered;
    }

    public function isEnrollmentPending(): bool
    {
        return $this->registrationStatus() === RosterRegistrationStatus::EnrollmentPending
            && ! $this->isFullyRegistered();
    }

    public function markEnrollmentPending(): void
    {
        $this->forceFill([
            'registration_status' => RosterRegistrationStatus::EnrollmentPending->value,
            'enrollment_pending_at' => now(),
            'is_registered' => false,
        ])->save();
    }

    public function markFullyRegistered(): void
    {
        $this->forceFill([
            'registration_status' => RosterRegistrationStatus::Registered->value,
            'is_registered' => true,
            'registered_at' => now(),
        ])->save();
    }

    public function markNotRegistered(): void
    {
        $this->forceFill([
            'registration_status' => RosterRegistrationStatus::NotRegistered->value,
            'is_registered' => false,
            'enrollment_pending_at' => null,
            'registered_at' => null,
        ])->save();
    }
}
