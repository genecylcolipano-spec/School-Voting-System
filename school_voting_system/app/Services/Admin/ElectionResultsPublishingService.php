<?php

namespace App\Services\Admin;

use App\Enums\AuditActionType;
use App\Enums\ElectionStatus;
use App\Models\Election;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ElectionResultsPublishingService
{
    public function __construct(
        protected AuditLogService $audit,
        protected PortalNotificationService $notifications,
    ) {}

    public function isReadyForReview(Election $election): bool
    {
        if ($election->public_results_published) {
            return false;
        }

        return $this->isVotingEnded($election);
    }

    public function isPublished(Election $election): bool
    {
        return (bool) $election->public_results_published;
    }

    public function publish(Election $election, User $actor): Election
    {
        if ($election->public_results_published) {
            throw new HttpException(422, 'Results are already published for this election.');
        }

        if (! $this->isVotingEnded($election)) {
            throw new HttpException(422, 'Results can only be published after voting has ended.');
        }

        $now = now();

        $status = $election->status === ElectionStatus::Archived
            ? ElectionStatus::Archived
            : ElectionStatus::Closed;

        $election->forceFill([
            'public_results_published' => true,
            'results_published_at' => $now,
            'results_published_by' => $actor->id,
            'results_locked' => true,
            'status' => $status,
            'voting_ends_at' => $election->voting_ends_at ?? $now,
        ])->save();

        $election->refreshIntegrityHash();

        $this->audit->record(
            $actor,
            "Published official election results: {$election->title}",
            AuditActionType::Election,
            targetType: 'election',
            targetId: $election->id,
            metadata: [
                'published_at' => $now->toIso8601String(),
                'published_by' => $actor->id,
                'published_by_name' => $actor->name,
            ],
        );

        $this->notifications->resultsPublished($election, $actor);

        return $election->fresh(['resultsPublisher']);
    }

    public function unpublish(Election $election, User $actor): Election
    {
        if (! $election->public_results_published) {
            throw new HttpException(422, 'Results are not published for this election.');
        }

        $election->forceFill([
            'public_results_published' => false,
            'results_published_at' => null,
            'results_published_by' => null,
        ])->save();

        $this->audit->record(
            $actor,
            "Unpublished official election results: {$election->title}",
            AuditActionType::Election,
            targetType: 'election',
            targetId: $election->id,
            metadata: [
                'unpublished_at' => now()->toIso8601String(),
                'unpublished_by' => $actor->id,
                'unpublished_by_name' => $actor->name,
            ],
        );

        $this->notifications->resultsUnpublished($election, $actor);

        return $election->fresh();
    }

    public function isVotingEnded(Election $election): bool
    {
        if (in_array($election->status, [ElectionStatus::Closed, ElectionStatus::Archived], true)) {
            return true;
        }

        return $election->voting_ends_at instanceof Carbon
            && now()->gt($election->voting_ends_at);
    }
}
