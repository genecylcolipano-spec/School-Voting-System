<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentEventStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendVotingRemindersJob;
use App\Http\Requests\Admin\Actions\ApproveEntryRequest;
use App\Http\Requests\Admin\Actions\ApprovePosterRequest;
use App\Http\Requests\Admin\Actions\ExportPreliminaryResultsRequest;
use App\Http\Requests\Admin\Actions\OpenTalentVotingRequest;
use App\Http\Requests\Admin\Actions\PauseElectionRequest;
use App\Http\Requests\Admin\Actions\PublishElectionResultsRequest;
use App\Http\Requests\Admin\Actions\PublishTalentResultsRequest;
use App\Http\Requests\Admin\Actions\RejectEntryRequest;
use App\Http\Requests\Admin\Actions\RejectPosterRequest;
use App\Http\Requests\Admin\Actions\ResolveComplaintRequest;
use App\Http\Requests\Admin\Actions\ResumeElectionRequest;
use App\Http\Requests\Admin\Actions\SendRemindersRequest;
use App\Http\Requests\Admin\Actions\UnpublishElectionResultsRequest;
use App\Http\Requests\Admin\Actions\VerifyCandidateRequest;
use App\Models\AdminComplaint;
use App\Services\Admin\ElectionResultsPublishingService;
use App\Models\AdminVerificationRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\PartylistPoster;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Enums\AnnouncementRelatedModule;
use App\Services\Admin\AdminScopeService;
use App\Services\Portal\AnnouncementService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use App\Services\Talent\TalentEventPublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminActionController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AuditLogService $audit,
        protected TalentEventPublishingService $publishing,
        protected ElectionResultsPublishingService $electionPublishing,
        protected PortalNotificationService $notifications,
        protected AnnouncementService $announcements,
        protected ElectionLifecycleService $elections,
    ) {}

    public function pauseElection(PauseElectionRequest $request, Election $election): RedirectResponse
    {
        $this->elections->pause($election, $request->user());

        return back()->with('success', 'Voting paused for your assigned election.');
    }

    public function resumeElection(ResumeElectionRequest $request, Election $election): RedirectResponse
    {
        $this->elections->resume($election, $request->user());

        return back()->with('success', 'Voting resumed.');
    }

    public function exportPreliminary(ExportPreliminaryResultsRequest $request): Response
    {
        $admin = $request->user();
        $election = $this->scope->assignedElection($admin);
        $stats = $this->scope->statistics($admin);
        $role = $admin->staffRole?->name ?? 'Admin';

        $html = view('admin.exports.preliminary-results', [
            'election' => $election,
            'stats' => $stats,
            'role' => $role,
            'generatedAt' => now()->toDayDateTimeString(),
        ])->render();

        $this->audit->record($admin, 'Exported unofficial preliminary results (scoped)', AuditActionType::Report);

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="unofficial-results-'.now()->format('Y-m-d').'.html"',
        ]);
    }

    public function sendReminders(SendRemindersRequest $request): RedirectResponse
    {
        SendVotingRemindersJob::dispatch($request->user()->id);

        return back()->with('success', 'Voting reminders are being sent to students in your scope.');
    }

    public function verifyCandidate(VerifyCandidateRequest $request, Candidate $candidate): RedirectResponse
    {
        $candidate->forceFill(['eligibility_status' => 'verified'])->save();

        AdminVerificationRequest::query()
            ->where('subject_type', Candidate::class)
            ->where('subject_id', $candidate->id)
            ->where('status', 'pending')
            ->update(['status' => 'resolved']);

        $this->audit->record($request->user(), "Verified candidate: {$candidate->display_name}", AuditActionType::Election, targetType: 'candidate', targetId: $candidate->id);

        return back()->with('success', 'Candidate eligibility verified.');
    }

    public function approvePoster(ApprovePosterRequest $request, PartylistPoster $poster): RedirectResponse
    {
        $poster->forceFill([
            'status' => PartylistPoster::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'review_reason' => null,
            'reviewed_at' => now(),
        ])->save();

        $this->audit->record(
            $request->user(),
            "Approved partylist poster: {$poster->title} ({$poster->partylist?->name})",
            AuditActionType::Election,
            targetType: 'partylist_poster',
            targetId: $poster->id,
        );

        return back()->with('success', 'Poster approved.');
    }

    public function rejectPoster(RejectPosterRequest $request, PartylistPoster $poster): RedirectResponse
    {
        $validated = $request->validated();

        $poster->forceFill([
            'status' => PartylistPoster::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'review_reason' => $validated['reason'],
            'reviewed_at' => now(),
        ])->save();

        $this->audit->record(
            $request->user(),
            "Rejected partylist poster: {$poster->title} — {$validated['reason']}",
            AuditActionType::Election,
            targetType: 'partylist_poster',
            targetId: $poster->id,
            metadata: ['reason' => $validated['reason']],
        );

        return back()->with('success', 'Poster rejected with reason logged.');
    }

    public function approveTalentEntry(ApproveEntryRequest $request, TalentEventEntry $entry): RedirectResponse
    {
        $entry->forceFill([
            'status' => TalentEventEntry::STATUS_APPROVED,
            'reviewed_by' => $request->user()->id,
            'review_reason' => null,
            'reviewed_at' => now(),
        ])->save();

        $this->publishing->publishIfReady($entry->talentEvent->fresh(), $request->user());

        $this->notifications->talentSubmissionApproved($entry->fresh()->loadMissing('talentEvent', 'student'), $request->user());

        $this->audit->record(
            $request->user(),
            "Approved talent entry: {$entry->display_name} ({$entry->talentEvent?->title})",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entry->id,
        );

        return back()->with('success', 'Talent event entry approved.');
    }

    public function rejectTalentEntry(RejectEntryRequest $request, TalentEventEntry $entry): RedirectResponse
    {
        $validated = $request->validated();

        $entry->forceFill([
            'status' => TalentEventEntry::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'review_reason' => $validated['reason'],
            'reviewed_at' => now(),
        ])->save();

        $this->notifications->talentSubmissionRejected($entry->fresh()->loadMissing('talentEvent', 'student'), $request->user());

        $this->audit->record(
            $request->user(),
            "Rejected talent entry: {$entry->display_name} — {$validated['reason']}",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entry->id,
            metadata: ['reason' => $validated['reason']],
        );

        return back()->with('success', 'Entry rejected with reason logged.');
    }

    public function updateTalentEntryStatus(Request $request, TalentEventEntry $entry): RedirectResponse
    {
        $this->scope->assertTalentEntryInScope($request->user(), $entry);

        $validated = $request->validate([
            'status' => ['required', 'in:withdrawn,disqualified,archived,pending'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $entry->forceFill([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'review_reason' => $validated['reason'] ?? null,
            'reviewed_at' => now(),
        ])->save();

        $this->audit->record(
            $request->user(),
            "Set talent entry status to {$validated['status']}: {$entry->display_name}",
            AuditActionType::Election,
            targetType: 'talent_event_entry',
            targetId: $entry->id,
            metadata: ['status' => $validated['status']],
        );

        return back()->with('success', "Entry marked as {$validated['status']}.");
    }

    public function publishElectionResults(PublishElectionResultsRequest $request, Election $election): RedirectResponse
    {
        $this->electionPublishing->publish($election, $request->user());
        $this->announcements->generateForResultsPublished(
            $election->title,
            AnnouncementRelatedModule::Election,
            $election->id,
            $request->user(),
        );

        return redirect()
            ->route('admin.results.election.show', $election)
            ->with('success', 'Official election results have been published to students.');
    }

    public function unpublishElectionResults(UnpublishElectionResultsRequest $request, Election $election): RedirectResponse
    {
        $this->electionPublishing->unpublish($election, $request->user());

        return redirect()
            ->route('admin.results.election.show', $election)
            ->with('success', 'Official election results have been unpublished.');
    }

    public function openTalentVoting(OpenTalentVotingRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $talentEvent->forceFill([
            'status' => TalentEventStatus::VotingOpen,
            'voting_starts_at' => now(),
            'voting_ends_at' => $talentEvent->voting_ends_at && $talentEvent->voting_ends_at->gt(now())
                ? $talentEvent->voting_ends_at
                : now()->addDays(3),
            'published_to_students' => true,
            'published_at' => $talentEvent->published_at ?? now(),
            'is_paused' => false,
        ])->save();

        $this->publishing->publish($talentEvent->fresh(), $request->user());

        $this->notifications->talentVotingOpened($talentEvent->fresh(), $request->user());

        $this->audit->record(
            $request->user(),
            "Opened student voting for talent event: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        return back()->with('success', 'Student voting is now open for this event.');
    }

    public function publishTalentResults(PublishTalentResultsRequest $request, TalentEvent $talentEvent): RedirectResponse
    {
        $talentEvent->forceFill([
            'status' => TalentEventStatus::ResultsPublished,
            'results_published_at' => now(),
            'results_published_by' => $request->user()->id,
        ])->save();

        $this->publishing->publish($talentEvent->fresh(), $request->user());

        $this->notifications->talentResultsPublished($talentEvent->fresh(), $request->user());
        $this->announcements->generateForResultsPublished(
            $talentEvent->title,
            AnnouncementRelatedModule::TalentCompetition,
            $talentEvent->id,
            $request->user(),
        );

        $this->audit->record(
            $request->user(),
            "Published talent event results: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        return back()->with('success', 'Talent event results published.');
    }

    public function resolveComplaint(ResolveComplaintRequest $request, AdminComplaint $complaint): RedirectResponse
    {
        $complaint->forceFill(['status' => 'resolved'])->save();

        $this->audit->record(
            $request->user(),
            "Resolved complaint: {$complaint->title}",
            AuditActionType::Election,
            targetType: 'admin_complaint',
            targetId: $complaint->id,
        );

        return back()->with('success', 'Complaint marked as resolved.');
    }
}
