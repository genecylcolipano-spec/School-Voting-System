<?php

namespace App\Services\Portal;

use App\Enums\AuditActionType;
use App\Enums\NotificationModule;
use App\Enums\UserRole;
use App\Jobs\FanOutPortalNotificationsJob;
use App\Models\AdminAssignment;
use App\Models\Election;
use App\Models\PortalNotification;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Services\SuperAdmin\AuditLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PortalNotificationService
{
    public function __construct(protected AuditLogService $audit) {}

    /** @var array<string, string> */
    protected array $icons = [
        'admin_election_created' => '🗳️',
        'admin_election_updated' => '✏️',
        'admin_election_deleted' => '🗑️',
        'admin_voting_started' => '🟢',
        'admin_voting_paused' => '⏸',
        'admin_voting_resumed' => '▶',
        'admin_voting_closed' => '🔒',
        'admin_results_published' => '🏆',
        'admin_results_unpublished' => '📋',
        'admin_talent_created' => '🏆',
        'admin_talent_updated' => '🏆',
        'admin_talent_results_published' => '🎉',
        'admin_fundraiser_created' => '💰',
        'admin_fundraiser_updated' => '💰',
        'admin_donation_received' => '💰',
        'admin_announcement' => '📢',
        'admin_system_settings' => '⚙️',
        'admin_user_created' => '👤',
        'admin_user_updated' => '👤',
        'admin_user_deleted' => '👤',
        'admin_roster_imported' => '👤',
        'admin_student_registered' => '👤',
        'admin_passkey_reset' => '🔒',
        'faculty_user_created' => '🧑‍🏫',
        'faculty_judge_assigned' => '🧑‍⚖️',
        'faculty_judging_open' => '🧑‍⚖️',
        'faculty_score_submitted' => '🧑‍⚖️',
        'faculty_schedule_changed' => '📅',
        'faculty_event_published' => '📅',
        'student_voting_open' => '🗳️',
        'student_voting_paused' => '⏸',
        'student_voting_resumed' => '▶',
        'student_voting_reminder' => '⏰',
        'student_voting_closed' => '🔒',
        'student_ballot_submitted' => '✅',
        'student_results_published' => '🏆',
        'student_talent_voting_open' => '🏆',
        'student_talent_voting_paused' => '⏸',
        'student_talent_voting_resumed' => '▶',
        'student_talent_voting_closed' => '🔒',
        'student_talent_published' => '🏆',
        'student_talent_results_published' => '🎉',
        'admin_talent_voting_paused' => '⏸',
        'admin_talent_voting_resumed' => '▶',
        'admin_talent_voting_closed' => '🔒',
        'student_fundraiser_published' => '💰',
        'student_donation_confirmed' => '💰',
        'student_announcement' => '📢',
        'student_registered' => '👤',
        'student_passkey_registered' => '🔒',
        'student_event_reminder' => '📅',
        'super_backup_completed' => '💾',
        'super_restore_completed' => '💾',
        'super_maintenance_enabled' => '⚙️',
        'super_maintenance_disabled' => '⚙️',
        'talent_judge_assigned' => '🧑‍⚖️',
        'announcement' => '📢',
        'reminder' => '⏰',
        'talent_voting' => '🏆',
        'info' => '📌',
    ];

    public function iconForType(string $type, ?NotificationModule $module = null): string
    {
        if (isset($this->icons[$type])) {
            return $this->icons[$type];
        }

        return $module?->icon() ?? '📌';
    }

    public function notifyUser(
        User $user,
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
        ?int $announcementId = null,
    ): PortalNotification {
        $notification = PortalNotification::query()->create([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'module' => $module?->value,
            'user_id' => $user->id,
            'recipient_role' => $user->role?->value ?? (string) $user->role,
            'related_id' => $relatedId,
            'announcement_id' => $announcementId,
            'created_by' => $author?->id,
        ]);

        return $notification;
    }

    /**
     * Fan-out to active students (per-user rows so read state is private).
     */
    public function notifyStudents(
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): int {
        return $this->fanOutToRole(
            UserRole::Student,
            $title,
            $message,
            $type,
            $author,
            $module,
            $relatedId,
        );
    }

    /**
     * Administrators only (excludes Super Admin).
     */
    public function notifyAdministrators(
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): int {
        return $this->fanOutToRole(
            UserRole::Admin,
            $title,
            $message,
            $type,
            $author,
            $module,
            $relatedId,
        );
    }

    public function notifyFaculty(
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): int {
        return $this->fanOutToRole(
            UserRole::Faculty,
            $title,
            $message,
            $type,
            $author,
            $module,
            $relatedId,
        );
    }

    /**
     * @param  Collection<int, User>|array<int, User>  $admins
     */
    public function notifyAdmins(
        Collection|array $admins,
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): void {
        foreach ($admins as $admin) {
            $this->notifyUser($admin, $title, $message, $type, $author, $module, $relatedId);
        }
    }

    public function notifyElectionAdmins(
        Election $election,
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = NotificationModule::Election,
    ): void {
        $this->notifyAdmins(
            $this->electionAdmins($election),
            $title,
            $message,
            $type,
            $author,
            $module,
            $election->id,
        );
    }

    public function notifySuperAdmins(
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): void {
        $superAdmins = User::query()
            ->where('role', UserRole::SuperAdmin)
            ->where('is_active', true)
            ->get();

        $this->notifyAdmins($superAdmins, $title, $message, $type, $author, $module, $relatedId);
    }

    /**
     * Administrators + Super Administrators (shared operational events).
     */
    public function notifyAllPortalAdmins(
        string $title,
        string $message,
        string $type,
        ?User $author = null,
        ?NotificationModule $module = null,
        ?int $relatedId = null,
    ): void {
        $admins = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where('is_active', true)
            ->get();

        $this->notifyAdmins($admins, $title, $message, $type, $author, $module, $relatedId);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->queryForUser($user)->unread()->count();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentForUser(User $user, int $limit = 8): Collection
    {
        return $this->queryForUser($user)
            ->limit($limit)
            ->get()
            ->map(fn (PortalNotification $notification) => $this->format($notification));
    }

    public function feedForUser(User $user, int $limit = 8): array
    {
        return [
            'unread_count' => $this->unreadCountFor($user),
            'items' => $this->recentForUser($user, $limit)->values()->all(),
        ];
    }

    public function paginateForUser(
        User $user,
        ?string $search = null,
        ?string $status = null,
        ?string $period = null,
        int $perPage = 20,
    ): LengthAwarePaginator {
        $query = $this->queryForUser($user);

        if ($search) {
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            });
        }

        if ($status === 'unread') {
            $query->unread();
        } elseif ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->startOfMonth());
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function markRead(User $user, PortalNotification $notification): void
    {
        abort_unless($this->userOwnsNotification($user, $notification), 403);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();

            $this->audit->record(
                $user,
                'Notification Read',
                AuditActionType::System,
                targetType: 'notification',
                targetId: $notification->id,
            );
        }
    }

    public function markAllReadFor(User $user): void
    {
        $this->queryForUser($user)->unread()->update(['read_at' => now()]);

        $this->audit->record(
            $user,
            'Notifications Marked Read',
            AuditActionType::System,
            metadata: ['scope' => 'all'],
        );
    }

    public function deleteForUser(User $user, PortalNotification $notification): void
    {
        abort_unless($this->userOwnsNotification($user, $notification), 403);

        $id = $notification->id;
        $notification->delete();

        $this->audit->record(
            $user,
            'Notification Deleted',
            AuditActionType::System,
            targetType: 'notification',
            targetId: $id,
        );
    }

    public function userOwnsNotification(User $user, PortalNotification $notification): bool
    {
        return (int) $notification->user_id === (int) $user->id;
    }

    public function electionCreated(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Election Created',
            "A new election \"{$election->title}\" has been created.",
            'admin_election_created',
            $actor,
        );
    }

    public function electionUpdated(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Election Updated',
            "Election \"{$election->title}\" has been updated.",
            'admin_election_updated',
            $actor,
        );
    }

    public function electionDeleted(Election $election, User $actor): void
    {
        $title = $election->title;

        $this->notifyElectionAdmins(
            $election,
            'Election Deleted',
            "Election \"{$title}\" has been deleted.",
            'admin_election_deleted',
            $actor,
        );
    }

    public function votingOpened(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Voting Started',
            "Voting is now open for {$election->title}.",
            'admin_voting_started',
            $actor,
        );

        $this->notifyStudents(
            'Voting is Open',
            "{$election->title} is now open. Cast your ballot before voting closes.",
            'student_voting_open',
            $actor,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function ballotSubmitted(User $student, Election $election, string $receiptToken): void
    {
        $this->notifyUser(
            $student,
            'Vote Submitted Successfully',
            "Your vote for {$election->title} has been successfully submitted.",
            'student_ballot_submitted',
            null,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function votingPaused(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Voting Paused',
            "Voting has been paused for {$election->title}.",
            'admin_voting_paused',
            $actor,
        );

        $this->notifyStudents(
            'Voting Temporarily Paused',
            "Voting for {$election->title} is temporarily unavailable. You will be notified when it resumes.",
            'student_voting_paused',
            $actor,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function votingResumed(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Voting Resumed',
            "Voting has resumed for {$election->title}.",
            'admin_voting_resumed',
            $actor,
        );

        $this->notifyStudents(
            'Voting Resumed',
            "Voting for {$election->title} has resumed. You can cast your ballot again.",
            'student_voting_resumed',
            $actor,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function votingClosed(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Voting Closed',
            "Voting has closed for {$election->title}. Review results before publishing.",
            'admin_voting_closed',
            $actor,
        );

        $this->notifyStudents(
            'Election Closed',
            "Voting for {$election->title} has closed.",
            'student_voting_closed',
            $actor,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function resultsPublished(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Results Published',
            "Official results for {$election->title} have been published.",
            'admin_results_published',
            $actor,
        );

        $this->notifyStudents(
            'Official Results Published',
            "The official results for {$election->title} are now available.",
            'student_results_published',
            $actor,
            NotificationModule::Election,
            $election->id,
        );
    }

    public function resultsUnpublished(Election $election, User $actor): void
    {
        $this->notifyElectionAdmins(
            $election,
            'Results Unpublished',
            "Official results for {$election->title} have been unpublished.",
            'admin_results_unpublished',
            $actor,
        );
    }

    public function talentCreated(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Competition Created',
            "Talent event \"{$event->title}\" has been created.",
            'admin_talent_created',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    public function talentUpdated(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Competition Updated',
            "Talent event \"{$event->title}\" has been updated.",
            'admin_talent_updated',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $assignedJudgeIds = $event->judges()->pluck('user_id');
        if ($assignedJudgeIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $assignedJudgeIds)
                ->where('is_active', true)
                ->each(function (User $faculty) use ($event, $actor) {
                    $this->notifyUser(
                        $faculty,
                        'Competition Schedule Changed',
                        "Details for \"{$event->title}\" were updated. Review the latest schedule.",
                        'faculty_schedule_changed',
                        $actor,
                        NotificationModule::Competition,
                        $event->id,
                    );
                });
        }
    }

    public function talentVotingOpened(TalentEvent $event, User $actor): void
    {
        $this->notifyStudents(
            'Talent Competition Published',
            "Voting is now open for {$event->title}.",
            'student_talent_voting_open',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $assignedJudgeIds = $event->judges()->pluck('user_id');
        if ($assignedJudgeIds->isNotEmpty()) {
            User::query()
                ->whereIn('id', $assignedJudgeIds)
                ->where('is_active', true)
                ->each(function (User $faculty) use ($event, $actor) {
                    $this->notifyUser(
                        $faculty,
                        'Judging Is Now Open',
                        "Judging is open for {$event->title}.",
                        'faculty_judging_open',
                        $actor,
                        NotificationModule::Judging,
                        $event->id,
                    );
                });
        }
    }

    public function talentVotingPaused(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Voting Paused',
            "Voting has been paused for {$event->title}.",
            'admin_talent_voting_paused',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $this->notifyStudents(
            'Talent Voting Temporarily Paused',
            "Voting for {$event->title} is temporarily unavailable. You will be notified when it resumes.",
            'student_talent_voting_paused',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    public function talentVotingResumed(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Voting Resumed',
            "Voting has resumed for {$event->title}.",
            'admin_talent_voting_resumed',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $this->notifyStudents(
            'Talent Voting Resumed',
            "Voting for {$event->title} has resumed. You can cast your vote again.",
            'student_talent_voting_resumed',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    public function talentVotingClosed(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Voting Closed',
            "Voting has closed for {$event->title}.",
            'admin_talent_voting_closed',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $this->notifyStudents(
            'Talent Voting Closed',
            "Voting for {$event->title} has closed.",
            'student_talent_voting_closed',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    public function talentResultsPublished(TalentEvent $event, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Talent Results Published',
            "Results for {$event->title} have been published.",
            'admin_talent_results_published',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );

        $this->notifyStudents(
            'Talent Competition Results Published',
            "Official results for {$event->title} are now available.",
            'student_talent_results_published',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    public function talentSubmissionReceived(TalentEventEntry $entry, User $actor): void
    {
        $event = $entry->talentEvent;

        if ($entry->student) {
            $this->notifyUser(
                $entry->student,
                'Submission Received',
                "Your entry \"{$entry->performance_title}\" for {$event?->title} has been received and is awaiting review.",
                'student_talent_submission_received',
                $actor,
                NotificationModule::Competition,
                $entry->id,
            );
        }

        if ($event) {
            $this->notifyAllPortalAdmins(
                'New Talent Submission',
                "{$entry->display_name} submitted an entry for {$event->title}.",
                'admin_talent_submission_received',
                $actor,
                NotificationModule::Competition,
                $entry->id,
            );
        }
    }

    public function talentSubmissionApproved(TalentEventEntry $entry, User $actor): void
    {
        if ($entry->student) {
            $this->notifyUser(
                $entry->student,
                'Submission Approved',
                "Your entry \"{$entry->performance_title}\" for {$entry->talentEvent?->title} has been approved.",
                'student_talent_submission_approved',
                $actor,
                NotificationModule::Competition,
                $entry->id,
            );
        }
    }

    public function talentSubmissionRejected(TalentEventEntry $entry, User $actor): void
    {
        if ($entry->student) {
            $reason = $entry->review_reason ? " Reason: {$entry->review_reason}" : '';

            $this->notifyUser(
                $entry->student,
                'Submission Rejected',
                "Your entry for {$entry->talentEvent?->title} was not approved.{$reason}",
                'student_talent_submission_rejected',
                $actor,
                NotificationModule::Competition,
                $entry->id,
            );
        }
    }

    public function talentVotingClosingSoon(TalentEvent $event, ?User $actor = null): void
    {
        $this->notifyStudents(
            'Talent Voting Closing Soon',
            "Voting for {$event->title} is closing soon. Cast your vote before it ends.",
            'student_talent_voting_closing_soon',
            $actor,
            NotificationModule::Competition,
            $event->id,
        );
    }

    /**
     * Hourly reminder: notify students when talent voting ends within the next window.
     * Dedupes per competition via existing portal_notifications rows.
     */
    public function dispatchTalentVotingClosingSoonReminders(int $withinHours = 24): int
    {
        $now = now();
        $deadline = $now->copy()->addHours(max(1, $withinHours));

        $events = TalentEvent::query()
            ->publishedToStudents()
            ->whereNotNull('voting_ends_at')
            ->where('voting_ends_at', '>', $now)
            ->where('voting_ends_at', '<=', $deadline)
            ->get();

        $dispatched = 0;

        foreach ($events as $event) {
            if (! $event->isWithinVotingWindow($now)) {
                continue;
            }

            $alreadySent = PortalNotification::query()
                ->where('type', 'student_talent_voting_closing_soon')
                ->where('related_id', $event->id)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $this->talentVotingClosingSoon($event);
            $dispatched++;
        }

        return $dispatched;
    }

    public function fundraiserCreated(string $title, User $actor, ?int $fundraiserId = null, bool $notifyStudents = false): void
    {
        $this->notifyAllPortalAdmins(
            'Fundraising Campaign Created',
            "Fundraiser \"{$title}\" has been created.",
            'admin_fundraiser_created',
            $actor,
            NotificationModule::Fundraising,
            $fundraiserId,
        );

        if ($notifyStudents) {
            $this->notifyStudents(
                'New Fundraising Campaign',
                "\"{$title}\" is now available. Support your school community.",
                'student_fundraiser_published',
                $actor,
                NotificationModule::Fundraising,
                $fundraiserId,
            );
        }
    }

    public function fundraiserUpdated(string $title, User $actor, ?int $fundraiserId = null, bool $notifyStudents = false): void
    {
        $this->notifyAllPortalAdmins(
            'Fundraising Campaign Updated',
            "Fundraiser \"{$title}\" has been updated.",
            'admin_fundraiser_updated',
            $actor,
            NotificationModule::Fundraising,
            $fundraiserId,
        );

        if ($notifyStudents) {
            $this->notifyStudents(
                'New Fundraising Campaign',
                "\"{$title}\" is now available. Support your school community.",
                'student_fundraiser_published',
                $actor,
                NotificationModule::Fundraising,
                $fundraiserId,
            );
        }
    }

    public function donationReceived(string $campaignTitle, float $amount, ?User $donor = null, ?User $actor = null, ?int $donationId = null): void
    {
        $this->notifyAllPortalAdmins(
            'Donation Received',
            'A donation of '.number_format($amount, 2).' was received for "'.$campaignTitle.'".',
            'admin_donation_received',
            $actor,
            NotificationModule::Fundraising,
            $donationId,
        );

        if ($donor) {
            $this->notifyUser(
                $donor,
                'Donation Confirmed',
                'Thank you! Your donation to "'.$campaignTitle.'" was received.',
                'student_donation_confirmed',
                $actor,
                NotificationModule::Fundraising,
                $donationId,
            );
        }
    }

    public function administratorChanged(string $title, string $message, string $type, User $actor): void
    {
        $this->notifySuperAdmins($title, $message, $type, $actor, NotificationModule::User);
    }

    public function administratorCreated(User $administrator, User $actor): void
    {
        $this->administratorChanged(
            'Administrator Account Created',
            "Administrator account {$administrator->name} has been created.",
            'admin_user_created',
            $actor,
        );
    }

    public function facultyCreated(User $faculty, User $actor): void
    {
        $this->administratorChanged(
            'Faculty Account Created',
            "Faculty account {$faculty->name} has been created.",
            'faculty_user_created',
            $actor,
        );
    }

    public function administratorUpdated(User $administrator, User $actor): void
    {
        $this->administratorChanged(
            'Administrator Updated',
            "Administrator account {$administrator->name} has been updated.",
            'admin_user_updated',
            $actor,
        );
    }

    public function administratorDeleted(string $name, string $accountId, User $actor): void
    {
        $this->administratorChanged(
            'Administrator Deleted',
            "Administrator account {$name} ({$accountId}) has been deleted.",
            'admin_user_deleted',
            $actor,
        );
    }

    public function studentRegistered(User $student, ?User $actor = null): void
    {
        $this->notifyUser(
            $student,
            'Registration Completed',
            'Your student account is ready. You can sign in with your passkey.',
            'student_registered',
            $actor,
            NotificationModule::Registration,
            $student->id,
        );

        $this->notifyAllPortalAdmins(
            'Student Registration Completed',
            "{$student->name} completed student registration.",
            'admin_student_registered',
            $actor,
            NotificationModule::Registration,
            $student->id,
        );
    }

    public function studentPasskeyRegistered(User $student): void
    {
        $this->notifyUser(
            $student,
            'Passkey Registered Successfully',
            'Your passkey was registered. You can use it to sign in securely.',
            'student_passkey_registered',
            $student,
            NotificationModule::Authentication,
            $student->id,
        );
    }

    public function rosterImported(int $importedCount, User $actor): void
    {
        $this->notifySuperAdmins(
            'Student Roster Imported',
            "Official roster import completed ({$importedCount} record(s)).",
            'admin_roster_imported',
            $actor,
            NotificationModule::Roster,
        );
    }

    public function passkeyResetCompleted(User $target, User $actor): void
    {
        $this->notifyAllPortalAdmins(
            'Passkey Reset Completed',
            "Passkey reset was issued for {$target->name}.",
            'admin_passkey_reset',
            $actor,
            NotificationModule::Security,
            $target->id,
        );
    }

    public function backupCompleted(string $label, User $actor, ?int $backupId = null): void
    {
        $this->notifySuperAdmins(
            'Backup Completed',
            "Backup \"{$label}\" was created successfully.",
            'super_backup_completed',
            $actor,
            NotificationModule::Backup,
            $backupId,
        );
    }

    public function restoreCompleted(string $label, User $actor, ?int $backupId = null): void
    {
        $this->notifySuperAdmins(
            'Restore Completed',
            "Backup \"{$label}\" was restored successfully.",
            'super_restore_completed',
            $actor,
            NotificationModule::Backup,
            $backupId,
        );
    }

    public function maintenanceEnabled(User $actor): void
    {
        $this->notifySuperAdmins(
            'Maintenance Mode Enabled',
            'The platform was placed into maintenance mode.',
            'super_maintenance_enabled',
            $actor,
            NotificationModule::System,
        );
    }

    public function maintenanceDisabled(User $actor): void
    {
        $this->notifySuperAdmins(
            'Maintenance Mode Disabled',
            'The platform is back online.',
            'super_maintenance_disabled',
            $actor,
            NotificationModule::System,
        );
    }

    public function facultyJudgeAssigned(
        User $faculty,
        TalentEvent $event,
        ?User $actor = null,
        ?\App\Models\TalentEventJudge $assignment = null,
    ): void {
        $role = $assignment?->roleLabel() ?? 'Judge';
        $category = $event->talent_category?->label() ?? $event->type?->label() ?? 'Talent';
        $date = optional($event->event_date)->format('F j, Y') ?? 'TBA';

        $this->notifyUser(
            $faculty,
            "You have been assigned as a Judge for {$event->title}.",
            "Competition: {$event->title}\nCategory: {$category}\nJudge Role: {$role}\nCompetition Date: {$date}\n\nOpen Assigned Competitions to begin judging.",
            'faculty_judge_assigned',
            $actor,
            NotificationModule::Judging,
            $event->id,
        );
    }

    public function superAdminJudgeAssignmentCompleted(
        User $superAdmin,
        User $faculty,
        TalentEvent $event,
        ?\App\Models\TalentEventJudge $assignment = null,
    ): void {
        $role = $assignment?->roleLabel() ?? 'Judge';

        $this->notifyUser(
            $superAdmin,
            'Judge assignment completed successfully.',
            "{$faculty->name} was assigned as {$role} for \"{$event->title}\".",
            'super_admin_judge_assignment_completed',
            $superAdmin,
            NotificationModule::Judging,
            $event->id,
        );
    }

    public function facultyJudgeRemoved(
        User $faculty,
        TalentEvent $event,
        ?User $actor = null,
        ?\App\Models\TalentEventJudge $assignment = null,
    ): void {
        $role = $assignment?->roleLabel() ?? 'Judge';

        $this->notifyUser(
            $faculty,
            'Judge Assignment Removed',
            "Your {$role} assignment for \"{$event->title}\" has been removed. You no longer have access to judge this competition.",
            'faculty_judge_removed',
            $actor,
            NotificationModule::Judging,
            $event->id,
        );
    }

    public function facultyJudgeRoleUpdated(
        User $faculty,
        TalentEvent $event,
        \App\Models\TalentEventJudge $assignment,
        ?User $actor = null,
    ): void {
        $this->notifyUser(
            $faculty,
            'Judge Role Updated',
            "Your role for \"{$event->title}\" is now {$assignment->roleLabel()}.",
            'faculty_judge_role_updated',
            $actor,
            NotificationModule::Judging,
            $event->id,
        );
    }

    public function facultyScoreSubmitted(User $faculty, TalentEvent $event): void
    {
        $this->notifyUser(
            $faculty,
            'Score Submission Successful',
            "Your scores for \"{$event->title}\" were submitted successfully.",
            'faculty_score_submitted',
            $faculty,
            NotificationModule::Judging,
            $event->id,
        );
    }

    public function schoolEventPublished(string $title, ?User $actor = null, ?int $eventId = null): void
    {
        $this->notifyFaculty(
            'School Event Published',
            "\"{$title}\" has been published.",
            'faculty_event_published',
            $actor,
            NotificationModule::Event,
            $eventId,
        );

        $this->notifyStudents(
            'School Event Reminder',
            "\"{$title}\" is coming up. Check the events page for details.",
            'student_event_reminder',
            $actor,
            NotificationModule::Event,
            $eventId,
        );
    }

    public function sendVotingReminder(User $student, User $actor, ?Election $election = null): void
    {
        if ($election) {
            $alreadySent = PortalNotification::query()
                ->where('user_id', $student->id)
                ->where('type', 'student_voting_reminder')
                ->where('related_id', $election->id)
                ->where('created_at', '>=', now()->subHours(12))
                ->exists();

            if ($alreadySent) {
                return;
            }
        }

        $title = $election?->title;
        $message = $title
            ? "Please complete your vote in \"{$title}\" before voting closes."
            : 'Please complete your vote in the assigned election before voting closes.';

        $this->notifyUser(
            $student,
            'Voting Reminder',
            $message,
            'student_voting_reminder',
            $actor,
            NotificationModule::Election,
            $election?->id,
        );
    }

    public function systemSettingsUpdated(User $actor): void
    {
        $this->notifySuperAdmins(
            'System Settings Updated',
            'Platform security or system settings were updated.',
            'admin_system_settings',
            $actor,
            NotificationModule::System,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function format(PortalNotification $notification): array
    {
        $module = $notification->module instanceof NotificationModule
            ? $notification->module
            : null;

        return [
            'id' => $notification->id,
            'icon' => $this->iconForType($notification->type, $module),
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'module' => $module?->value,
            'related_id' => $notification->related_id,
            'url' => $this->actionUrlFor($notification),
            'read' => $notification->read_at !== null,
            'time_ago' => $notification->created_at?->diffForHumans() ?? '—',
            'date' => $notification->created_at?->format('M d, Y') ?? '—',
            'time' => $notification->created_at?->format('g:i A') ?? '—',
        ];
    }

    /**
     * Deep-link target for a notification, scoped by type + recipient role.
     */
    public function actionUrlFor(PortalNotification $notification): ?string
    {
        $type = $notification->type;
        $role = $notification->recipient_role instanceof UserRole
            ? $notification->recipient_role->value
            : (string) ($notification->recipient_role ?? '');
        $relatedId = $notification->related_id ? (int) $notification->related_id : null;
        $announcementId = $notification->announcement_id
            ? (int) $notification->announcement_id
            : $relatedId;

        try {
            if (in_array($type, ['student_announcement', 'admin_announcement'], true) && $announcementId) {
                $announcement = \App\Models\Announcement::query()->find($announcementId);
                if (! $announcement) {
                    return null;
                }

                return match ($role) {
                    UserRole::Student->value => route('student.announcements.show', $announcement),
                    UserRole::Faculty->value => route('faculty.announcements.show', $announcement),
                    default => route('admin.announcements.preview', $announcement),
                };
            }

            if (
                $relatedId
                && (
                    str_starts_with($type, 'student_voting')
                    || in_array($type, ['student_ballot_submitted', 'student_voting_reminder', 'student_results_published'], true)
                )
            ) {
                $election = Election::query()->find($relatedId);
                if ($election) {
                    return $type === 'student_results_published'
                        ? route('student.results.election.show', $election)
                        : route('student.voting.show', $election);
                }
            }

            if ($relatedId && str_contains($type, 'submission')) {
                $entry = TalentEventEntry::query()->with('talentEvent')->find($relatedId);
                if ($entry) {
                    return match (true) {
                        str_starts_with($type, 'student_') => route('student.talent-registration.entry.show', $entry),
                        default => route('admin.talent-participants.show', $entry),
                    };
                }
            }

            if (
                $relatedId
                && (
                    str_contains($type, 'talent')
                    || str_starts_with($type, 'faculty_judge')
                    || str_starts_with($type, 'faculty_judging')
                    || str_starts_with($type, 'faculty_score')
                    || str_starts_with($type, 'faculty_schedule')
                )
            ) {
                $event = TalentEvent::query()->find($relatedId);
                if (! $event) {
                    return null;
                }

                return match (true) {
                    str_starts_with($type, 'student_talent_results') => route('student.results.talent.show', $event),
                    str_starts_with($type, 'student_') => route('student.talent-voting.show', $event),
                    str_starts_with($type, 'faculty_') => route('faculty.judging.show', $event),
                    default => route('admin.talent-competition.show', $event),
                };
            }

            if (in_array($type, ['student_fundraiser_published', 'student_donation_confirmed', 'admin_fundraiser_created', 'admin_fundraiser_updated', 'admin_donation_received'], true) && $relatedId) {
                $fundraiser = \App\Models\Fundraiser::query()->find($relatedId);
                if (! $fundraiser) {
                    return null;
                }

                return $role === UserRole::Student->value
                    ? route('student.fundraising.show', $fundraiser)
                    : route('admin.fundraisers.edit', $fundraiser);
            }

            if (str_starts_with($type, 'admin_election') || in_array($type, ['admin_voting_started', 'admin_voting_paused', 'admin_voting_resumed', 'admin_voting_closed', 'admin_results_published', 'admin_results_unpublished'], true)) {
                return $relatedId
                    ? route('admin.elections.edit', $relatedId)
                    : route('admin.live.election');
            }

            if (str_starts_with($type, 'faculty_event') || $type === 'student_event_reminder') {
                return $role === UserRole::Faculty->value
                    ? route('faculty.elections.index')
                    : route('student.announcements.index');
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    protected function queryForUser(User $user): Builder
    {
        return PortalNotification::query()->forUser($user);
    }

    /**
     * Dispatch role fan-out asynchronously (sync queue still runs inline in tests).
     */
    protected function fanOutToRole(
        UserRole $role,
        string $title,
        string $message,
        string $type,
        ?User $author,
        ?NotificationModule $module,
        ?int $relatedId,
    ): int {
        $recipientCount = User::query()
            ->where('role', $role)
            ->where('is_active', true)
            ->count();

        if ($recipientCount === 0) {
            return 0;
        }

        FanOutPortalNotificationsJob::dispatch(
            $role->value,
            $title,
            $message,
            $type,
            $module?->value,
            $relatedId,
            $author?->id,
        );

        return $recipientCount;
    }

    /**
     * Synchronous bulk insert used by FanOutPortalNotificationsJob.
     */
    public function insertFanOutForRole(
        UserRole $role,
        string $title,
        string $message,
        string $type,
        ?int $authorId,
        ?NotificationModule $module,
        ?int $relatedId,
    ): int {
        $count = 0;
        $now = now();

        User::query()
            ->where('role', $role)
            ->where('is_active', true)
            ->select(['id', 'role'])
            ->orderBy('id')
            ->chunkById(200, function ($users) use (&$count, $title, $message, $type, $authorId, $module, $relatedId, $role, $now) {
                $rows = [];
                foreach ($users as $user) {
                    $rows[] = [
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'module' => $module?->value,
                        'user_id' => $user->id,
                        'recipient_role' => $role->value,
                        'related_id' => $relatedId,
                        'created_by' => $authorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('portal_notifications')->insert($rows);
                    $count += count($rows);
                }
            });

        return $count;
    }

    /**
     * @return Collection<int, User>
     */
    protected function electionAdmins(Election $election): Collection
    {
        $adminIds = AdminAssignment::query()
            ->where('election_id', $election->id)
            ->pluck('user_id');

        return User::query()
            ->where('is_active', true)
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where(function ($query) use ($adminIds) {
                $query->where('role', UserRole::SuperAdmin)
                    ->orWhereIn('id', $adminIds);
            })
            ->get();
    }
}
