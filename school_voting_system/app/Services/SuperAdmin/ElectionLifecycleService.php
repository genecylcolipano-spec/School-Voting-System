<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Str;

class ElectionLifecycleService
{
    public function __construct(
        protected AuditLogService $audit,
        protected PortalNotificationService $notifications,
    ) {}

    public function open(Election $election, User $actor): Election
    {
        // Already live but paused → resume (do not re-blast "Voting is Open").
        if ($election->status === ElectionStatus::Active && $election->is_paused) {
            return $this->resume($election, $actor);
        }

        // Already accepting votes — idempotent (no duplicate student fan-out).
        if ($election->status === ElectionStatus::Active && ! $election->is_paused) {
            return $election;
        }

        // Re-opening voting must retract any previously published official
        // results. An election cannot be actively accepting votes while its
        // official results are published, otherwise late votes would corrupt
        // results students have already seen.
        $election->forceFill([
            'status' => ElectionStatus::Active,
            'is_paused' => false,
            'results_locked' => false,
            'public_results_published' => false,
            'results_published_at' => null,
            'voting_starts_at' => $election->voting_starts_at ?? now(),
        ])->save();

        $this->audit->record($actor, "Opened election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        $this->notifications->votingOpened($election, $actor);

        return $election->fresh();
    }

    public function pause(Election $election, User $actor): Election
    {
        $election->forceFill(['is_paused' => true])->save();
        $this->audit->record($actor, "Paused election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        $this->notifications->votingPaused($election, $actor);

        return $election->fresh();
    }

    public function resume(Election $election, User $actor): Election
    {
        $election->forceFill([
            'status' => ElectionStatus::Active,
            'is_paused' => false,
        ])->save();

        $this->audit->record($actor, "Resumed election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        $this->notifications->votingResumed($election, $actor);

        return $election->fresh();
    }

    public function close(Election $election, User $actor): Election
    {
        $election->forceFill([
            'status' => ElectionStatus::Closed,
            'is_paused' => false,
            'results_locked' => true,
            'voting_ends_at' => $election->voting_ends_at ?? now(),
        ])->save();

        $election->refreshIntegrityHash();
        $this->audit->record($actor, "Closed election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        $this->notifications->votingClosed($election, $actor);

        return $election->fresh();
    }

    public function annul(Election $election, User $actor): Election
    {
        $election->forceFill([
            'status' => ElectionStatus::Closed,
            'annulled_at' => now(),
            'is_paused' => true,
        ])->save();

        $this->audit->record($actor, "Annulled election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        return $election->fresh();
    }

    public function rerun(Election $election, User $actor): Election
    {
        $rerun = Election::query()->create([
            'title' => $election->title.' (Re-run)',
            'slug' => Str::slug($election->title.'-rerun-'.now()->format('YmdHis')),
            'description' => $election->description,
            'status' => ElectionStatus::Draft,
            'created_by' => $actor->id,
            'rerun_parent_id' => $election->id,
        ]);

        $this->audit->record($actor, "Re-run created from election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $rerun->id);

        return $rerun;
    }

    public function lockResults(Election $election, User $actor, bool $locked = true): Election
    {
        $election->forceFill(['results_locked' => $locked])->save();

        if ($locked) {
            $election->refreshIntegrityHash();
        }

        $this->audit->record(
            $actor,
            ($locked ? 'Locked' : 'Unlocked')." results for: {$election->title}",
            AuditActionType::Election,
            targetType: 'election',
            targetId: $election->id,
        );

        return $election->fresh();
    }

    public function schedule(Election $election, User $actor, ?string $openAt, ?string $closeAt): Election
    {
        $election->forceFill([
            'scheduled_open_at' => $openAt,
            'scheduled_close_at' => $closeAt,
            'voting_starts_at' => $openAt ?? $election->voting_starts_at,
            'voting_ends_at' => $closeAt ?? $election->voting_ends_at,
        ])->save();

        $this->audit->record($actor, "Scheduled election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id, metadata: [
            'open' => $openAt,
            'close' => $closeAt,
        ]);

        return $election->fresh();
    }
}
