<?php

namespace App\Services\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentEventStatus;
use App\Models\TalentEvent;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\Talent\TalentEventPublishingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TalentResultsPublishingService
{
    public function __construct(
        protected AuditLogService $audit,
        protected PortalNotificationService $notifications,
        protected TalentEventPublishingService $publishing,
    ) {}

    public function isPublished(TalentEvent $event): bool
    {
        return $event->hasPublishedResults();
    }

    public function isReadyForReview(TalentEvent $event): bool
    {
        if ($this->isPublished($event) || $event->is_paused) {
            return false;
        }

        if ($event->isAcceptingVotes() || $event->isAcceptingJudgeScores()) {
            return false;
        }

        return $this->hasStarted($event);
    }

    public function publish(TalentEvent $event, User $actor): TalentEvent
    {
        if ($this->isPublished($event)) {
            throw new HttpException(422, 'Results are already published for this competition.');
        }

        if (! $this->isReadyForReview($event)) {
            throw new HttpException(422, 'Results can only be published after voting and judging have ended.');
        }

        $now = now();

        $event->forceFill([
            'status' => TalentEventStatus::ResultsPublished,
            'results_published_at' => $now,
            'results_published_by' => $actor->id,
            'voting_ends_at' => $event->voting_ends_at ?? $now,
            'is_paused' => false,
        ])->save();

        $this->publishing->publish($event->fresh(), $actor);

        $this->audit->record(
            $actor,
            "Published talent event results: {$event->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $event->id,
            metadata: [
                'published_at' => $now->toIso8601String(),
                'published_by' => $actor->id,
            ],
        );

        $this->notifications->talentResultsPublished($event->fresh(), $actor);

        return $event->fresh(['resultsPublisher']);
    }

    public function unpublish(TalentEvent $event, User $actor): TalentEvent
    {
        if (! $this->isPublished($event)) {
            throw new HttpException(422, 'Results are not published for this competition.');
        }

        $status = $event->status === TalentEventStatus::Completed
            ? TalentEventStatus::Completed
            : TalentEventStatus::VotingOpen;

        $event->forceFill([
            'status' => $status,
            'results_published_at' => null,
            'results_published_by' => null,
        ])->save();

        $this->audit->record(
            $actor,
            "Unpublished talent event results: {$event->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $event->id,
            metadata: [
                'unpublished_at' => now()->toIso8601String(),
                'unpublished_by' => $actor->id,
            ],
        );

        $this->notifications->talentResultsUnpublished($event->fresh(), $actor);

        return $event->fresh();
    }

    protected function hasStarted(TalentEvent $event): bool
    {
        if ($event->status === TalentEventStatus::VotingOpen
            || $event->status === TalentEventStatus::ResultsPublished
            || $event->status === TalentEventStatus::Completed) {
            return true;
        }

        if ($event->voting_starts_at && now()->gte($event->voting_starts_at)) {
            return true;
        }

        return $event->votingHasClosed();
    }
}
