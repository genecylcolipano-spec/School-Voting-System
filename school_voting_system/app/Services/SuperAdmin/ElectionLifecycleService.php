<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        if ($election->annulled_at) {
            throw new HttpException(422, 'An annulled election cannot be opened.');
        }

        if ($election->status === ElectionStatus::Archived) {
            throw new HttpException(422, 'An archived election cannot be opened.');
        }

        // Already accepting votes — do not report success for a no-op.
        if ($election->status === ElectionStatus::Active && ! $election->is_paused) {
            throw new HttpException(422, 'This election is already open.');
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
        if ($election->status !== ElectionStatus::Active || $election->is_paused || $election->annulled_at) {
            throw new HttpException(422, 'Only an open election can be paused.');
        }

        $election->forceFill(['is_paused' => true])->save();
        $this->audit->record($actor, "Paused election: {$election->title}", AuditActionType::Election, targetType: 'election', targetId: $election->id);

        $this->notifications->votingPaused($election, $actor);

        return $election->fresh();
    }

    public function resume(Election $election, User $actor): Election
    {
        if ($election->status !== ElectionStatus::Active || ! $election->is_paused || $election->annulled_at) {
            throw new HttpException(422, 'Only a paused election can be resumed.');
        }

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
        if ($election->status !== ElectionStatus::Active || $election->annulled_at) {
            throw new HttpException(422, 'Only an open or paused election can be closed.');
        }

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
        if ($election->annulled_at) {
            throw new HttpException(422, 'This election is already annulled.');
        }

        if ($election->status === ElectionStatus::Draft) {
            throw new HttpException(422, 'A draft election cannot be annulled. Delete it from Elections instead.');
        }

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
        if (! in_array($election->status, [ElectionStatus::Closed, ElectionStatus::Archived], true) && ! $election->annulled_at) {
            throw new HttpException(422, 'Re-run is available after an election is closed or annulled.');
        }

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
        if ($locked && $election->results_locked) {
            throw new HttpException(422, 'Results are already locked.');
        }

        if (! $locked && ! $election->results_locked) {
            throw new HttpException(422, 'Results are not locked.');
        }

        if ($locked && $election->status === ElectionStatus::Draft) {
            throw new HttpException(422, 'Results can only be locked after voting has started or closed.');
        }

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
        if (! $this->canSchedule($election)) {
            throw new HttpException(422, 'This election cannot be scheduled.');
        }

        if (! $openAt && ! $closeAt) {
            throw new HttpException(422, 'Choose an open or close time to schedule.');
        }

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

    public function canSchedule(Election $election): bool
    {
        return $election->annulled_at === null
            && $election->status !== ElectionStatus::Archived;
    }

    /**
     * @return array<string, string>
     */
    public function availableActions(Election $election): array
    {
        if ($election->annulled_at) {
            return ['rerun' => 'Re-run'];
        }

        $actions = [];
        $votingEnded = in_array($election->status, [ElectionStatus::Closed, ElectionStatus::Archived], true)
            || ($election->voting_ends_at && now()->gt($election->voting_ends_at));

        if ($election->status === ElectionStatus::Draft) {
            $actions['open'] = 'Open';
        }

        if ($election->status === ElectionStatus::Active && $election->is_paused) {
            $actions['resume'] = 'Resume';
            $actions['close'] = 'Close';
            $actions['annul'] = 'Annul';
        } elseif ($election->status === ElectionStatus::Active) {
            $actions['pause'] = 'Pause';
            $actions['close'] = 'Close';
            $actions['annul'] = 'Annul';
        }

        if ($election->status === ElectionStatus::Closed) {
            $actions['open'] = 'Re-open';
            $actions['rerun'] = 'Re-run';
            $actions['annul'] = 'Annul';
        }

        if ($election->status === ElectionStatus::Archived) {
            $actions['rerun'] = 'Re-run';
        }

        if (! $election->results_locked && $election->status !== ElectionStatus::Draft) {
            $actions['lock'] = 'Lock Results';
        }

        if ($votingEnded && ! $election->public_results_published && $election->status !== ElectionStatus::Draft) {
            $actions['publish_results'] = 'Publish Results';
        }

        if ($election->public_results_published) {
            $actions['unpublish_results'] = 'Unpublish';
        }

        return $actions;
    }
}
