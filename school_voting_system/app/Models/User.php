<?php

namespace App\Models;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable;

    protected $fillable = [
        'account_id',
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar_path',
        'password',
        'role',
        'staff_role_id',
        'grade_level',
        'section',
        'student_status',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'archived_at' => 'datetime',
            'role' => UserRole::class,
            'student_status' => StudentStatus::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Exact case-sensitive lookup — no trimming, no type conversion.
     */
    public static function findByAccountId(string $accountId): ?self
    {
        return static::query()
            ->where('account_id', $accountId)
            ->first();
    }

    public function staffRole(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class);
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->staffRole?->hasPermission($key) ?? false;
    }

    public function isEligibleVoter(): bool
    {
        return $this->isStudent()
            && $this->is_active
            && ($this->student_status?->canVote() ?? false);
    }

    public function assignRole(UserRole $role): void
    {
        $this->forceFill(['role' => $role])->save();
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isFaculty(): bool
    {
        return $this->role === UserRole::Faculty;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function avatarUrl(): ?string
    {
        if (! filled($this->avatar_path)) {
            return null;
        }

        $url = asset('storage/'.ltrim($this->avatar_path, '/'));

        // Bust browser cache after profile photo changes (same path edge cases).
        if ($this->updated_at) {
            $url .= '?v='.$this->updated_at->getTimestamp();
        }

        return $url;
    }

    /**
     * Two-letter initials for avatar fallbacks (e.g. "Jane Doe" → "JD").
     */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $parts = array_values(array_filter($parts));

        if ($parts === []) {
            return 'U';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }

        return mb_strtoupper(
            mb_substr($parts[0], 0, 1).mb_substr($parts[array_key_last($parts)], 0, 1)
        );
    }

    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Administrator';
        }

        if ($this->isAdmin()) {
            return 'Administrator';
        }

        if ($this->isFaculty()) {
            return 'Faculty';
        }

        return 'Student';
    }

    /**
     * Account lifecycle label for User Management lists.
     */
    public function accountStatusLabel(): string
    {
        if ($this->archived_at !== null) {
            return 'Deactivated';
        }

        if (! $this->is_active) {
            return 'Suspended';
        }

        return 'Active';
    }

    public function facultyRoster(): HasOne
    {
        return $this->hasOne(AllowedFaculty::class, 'account_id', 'account_id');
    }

    public function administratorRoster(): HasOne
    {
        return $this->hasOne(AllowedAdministrator::class, 'account_id', 'account_id');
    }

    public function studentRoster(): HasOne
    {
        return $this->hasOne(AllowedStudent::class, 'account_id', 'account_id');
    }

    public function departmentLabel(): string
    {
        if ($this->isFaculty()) {
            return $this->facultyRoster?->department
                ?: ($this->staffRole?->name ?? '—');
        }

        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return $this->administratorRoster?->department
                ?: ($this->staffRole?->name ?? '—');
        }

        return '—';
    }

    public function canVote(): bool
    {
        return $this->role?->canVote() ?? false;
    }

    public function canDonate(): bool
    {
        return $this->role?->canDonate() ?? false;
    }

    public function getPasskeyUsername(): string
    {
        return $this->account_id ?? $this->email ?? (string) $this->getAuthIdentifier();
    }

    public function getPasskeyDisplayName(): string
    {
        return $this->name;
    }

    /** @return HasMany<Passkey> */
    public function registeredPasskeys(): HasMany
    {
        return $this->passkeys();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function candidacies(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function createdElections(): HasMany
    {
        return $this->hasMany(Election::class, 'created_by');
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function createdFundraisers(): HasMany
    {
        return $this->hasMany(Fundraiser::class, 'created_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function judgingAssignments(): HasMany
    {
        return $this->hasMany(TalentEventJudge::class)->active();
    }

    public function hasVotedInCategory(ElectionCategory $category): bool
    {
        return $this->votes()
            ->where('election_category_id', $category->id)
            ->exists();
    }
}
