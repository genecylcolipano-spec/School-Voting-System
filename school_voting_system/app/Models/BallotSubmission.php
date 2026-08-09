<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BallotSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'election_id',
        'receipt_token',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Record a ballot receipt once the student has completed every votable position.
     */
    public static function recordFor(User $user, Election $election): self
    {
        $existing = static::query()
            ->where('user_id', $user->id)
            ->where('election_id', $election->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        if (! $election->hasStudentCompletedBallot($user)) {
            throw new \InvalidArgumentException('Ballot is not complete.');
        }

        return static::query()->create([
            'user_id' => $user->id,
            'election_id' => $election->id,
            'receipt_token' => static::generateToken(),
            'submitted_at' => now(),
        ]);
    }

    public static function generateToken(): string
    {
        return 'BR-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
    }
}
