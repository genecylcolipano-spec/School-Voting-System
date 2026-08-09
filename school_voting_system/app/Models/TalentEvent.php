<?php

namespace App\Models;

use App\Enums\TalentCategory;
use App\Enums\TalentEventStatus;
use App\Enums\TalentEventType;
use App\Enums\TalentRankingMethod;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentSubmissionMethod;
use App\Enums\TalentVotingMethod;
use App\Models\Concerns\HasEventImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TalentEvent extends Model
{
    use HasEventImage;
    use SoftDeletes;

    protected $fillable = [
        'election_id',
        'title',
        'slug',
        'competition_code',
        'type',
        'talent_category',
        'description',
        'image_path',
        'image_variants',
        'thumbnail_path',
        'poster_path',
        'event_date',
        'venue',
        'organizer',
        'max_performance_duration_minutes',
        'max_contestants',
        'voting_method',
        'judge_percentage',
        'student_vote_percentage',
        'number_of_winners',
        'ranking_method',
        'status',
        'is_paused',
        'auto_status_updates',
        'voting_starts_at',
        'voting_ends_at',
        'results_publish_at',
        'registration_starts_at',
        'registration_ends_at',
        'submission_deadline',
        'registration_method',
        'submission_method',
        'max_video_duration_seconds',
        'max_upload_size_mb',
        'accepted_video_formats',
        'results_published_at',
        'results_published_by',
        'published_to_students',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => TalentEventType::class,
            'talent_category' => TalentCategory::class,
            'voting_method' => TalentVotingMethod::class,
            'registration_method' => TalentRegistrationMethod::class,
            'submission_method' => TalentSubmissionMethod::class,
            'ranking_method' => TalentRankingMethod::class,
            'status' => TalentEventStatus::class,
            'event_date' => 'datetime',
            'voting_starts_at' => 'datetime',
            'voting_ends_at' => 'datetime',
            'results_publish_at' => 'datetime',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
            'submission_deadline' => 'datetime',
            'max_video_duration_seconds' => 'integer',
            'max_upload_size_mb' => 'integer',
            'results_published_at' => 'datetime',
            'published_to_students' => 'boolean',
            'published_at' => 'datetime',
            'is_paused' => 'boolean',
            'auto_status_updates' => 'boolean',
            'image_variants' => 'array',
        ];
    }

    public function scopePublishedToStudents($query)
    {
        return $query->where('published_to_students', true);
    }

    public function isPublishedToStudents(): bool
    {
        return (bool) $this->published_to_students;
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resultsPublisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'results_published_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TalentEventEntry::class);
    }

    public function approvedEntries(): HasMany
    {
        return $this->entries()->where('status', TalentEventEntry::STATUS_APPROVED);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(TalentEventVote::class);
    }

    public function judges(): HasMany
    {
        return $this->hasMany(TalentEventJudge::class)->active();
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(TalentEventJudge::class);
    }

    public function judgingCriteria(): HasMany
    {
        return $this->hasMany(TalentJudgingCriterion::class)->orderBy('sort_order');
    }

    public function judgeScoreSheets(): HasMany
    {
        return $this->hasMany(TalentJudgeScoreSheet::class);
    }

    public function requiresJudges(): bool
    {
        return in_array($this->voting_method, [
            TalentVotingMethod::JudgesOnly,
            TalentVotingMethod::JudgesAndStudents,
        ], true);
    }

    /**
     * Academic year label derived from competition date (e.g. 2026-2027).
     */
    public function schoolYearLabel(): string
    {
        $anchor = $this->event_date
            ?? $this->voting_starts_at
            ?? $this->registration_starts_at
            ?? now();

        $year = (int) $anchor->format('Y');
        $month = (int) $anchor->format('n');

        // School year rolls in June (typical PH academic calendar).
        if ($month >= 6) {
            return $year.'-'.($year + 1);
        }

        return ($year - 1).'-'.$year;
    }

    /**
     * Compact phase label for Super Admin assignment dropdowns.
     */
    public function assignmentPhaseLabel(): string
    {
        if ($this->isAcceptingJudgeScores() || $this->isAcceptingVotes()) {
            return 'Judging';
        }

        if ($this->isRegistrationOpen()) {
            return 'Registration Open';
        }

        if ($this->registration_ends_at && now()->greaterThan($this->registration_ends_at)) {
            return 'Registration Closed';
        }

        return $this->displayStatusLabel();
    }

    public function isAcceptingJudgeScores(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->requiresJudges()) {
            return false;
        }

        if ($this->is_paused) {
            return false;
        }

        if ($this->isArchived()
            || $this->status === TalentEventStatus::ResultsPublished
            || $this->status === TalentEventStatus::Completed
            || $this->results_published_at !== null) {
            return false;
        }

        return $this->isWithinVotingWindow($at)
            || ($this->voting_starts_at === null
                && $this->voting_ends_at === null
                && $this->status === TalentEventStatus::VotingOpen);
    }

    public function isAcceptingVotes(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        // Judges-only competitions do not accept student votes.
        if ($this->voting_method === TalentVotingMethod::JudgesOnly) {
            return false;
        }

        if ($this->is_paused) {
            return false;
        }

        if (! $this->published_to_students) {
            return false;
        }

        if ($this->isArchived()
            || $this->status === TalentEventStatus::ResultsPublished
            || $this->results_published_at !== null) {
            return false;
        }

        // Registration comes first: do not accept votes while registration is still open
        // and the voting window has not actually started.
        if ($this->isRegistrationOpen($at) && ! $this->votingWindowHasStarted($at)) {
            return false;
        }

        // Prefer the configured voting window so voting opens/closes by schedule
        // without requiring a manual status change to voting_open.
        if ($this->voting_starts_at !== null) {
            return $this->isWithinVotingWindow($at);
        }

        // End date alone must not open voting early — require explicit Voting Open status.
        if ($this->voting_ends_at !== null) {
            if ($at->gt($this->voting_ends_at)) {
                return false;
            }

            return $this->status === TalentEventStatus::VotingOpen;
        }

        // Fallback when no schedule is set: explicit Voting Open status only.
        return $this->status === TalentEventStatus::VotingOpen;
    }

    /**
     * True when the scheduled voting start has been reached (or no start is configured).
     */
    public function votingWindowHasStarted(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->voting_starts_at === null) {
            return false;
        }

        return $at->gte($this->voting_starts_at);
    }

    /**
     * True when now is inside [voting_starts_at, voting_ends_at].
     * Requires voting_starts_at — a lone end date never means "open".
     */
    public function isWithinVotingWindow(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->voting_starts_at === null) {
            return false;
        }

        if ($at->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at !== null && $at->gt($this->voting_ends_at)) {
            return false;
        }

        return true;
    }

    public function isBeforeVotingStart(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        return $this->voting_starts_at !== null && $at->lt($this->voting_starts_at);
    }

    public function isAfterVotingEnd(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        return $this->voting_ends_at !== null && $at->gt($this->voting_ends_at);
    }

    public function votingHasClosed(): bool
    {
        if ($this->status === TalentEventStatus::ResultsPublished
            || $this->status === TalentEventStatus::Completed
            || $this->results_published_at !== null) {
            return true;
        }

        return $this->isAfterVotingEnd();
    }

    public function displayStatusLabel(): string
    {
        return $this->currentStatusLabel();
    }

    public function currentStatusKey(?\Illuminate\Support\Carbon $at = null): string
    {
        return app(\App\Services\Talent\TalentCompetitionStatusResolver::class)->key($this, $at);
    }

    public function currentStatusLabel(?\Illuminate\Support\Carbon $at = null): string
    {
        return app(\App\Services\Talent\TalentCompetitionStatusResolver::class)->label($this, $at);
    }

    /**
     * @return array{key: string, label: string}
     */
    public function currentStatus(?\Illuminate\Support\Carbon $at = null): array
    {
        return app(\App\Services\Talent\TalentCompetitionStatusResolver::class)->resolve($this, $at);
    }

    public function bannerUrl(): ?string
    {
        return $this->cardBannerUrl();
    }

    /**
     * Landscape banner for cards/dashboards. Portrait/square uploads
     * are never shown here — cards stay 16:9 with object-cover.
     */
    public function cardBannerUrl(): string
    {
        if ($this->hasLandscapeCompetitionBanner()) {
            return $this->image_url;
        }

        return \App\Support\EventImageUrl::placeholder();
    }

    public function cardBannerMediumUrl(): string
    {
        if (! $this->hasLandscapeCompetitionBanner()) {
            return \App\Support\EventImageUrl::placeholder();
        }

        return $this->bannerMediumUrl() ?? $this->image_url;
    }

    public function cardBannerMobileUrl(): string
    {
        if (! $this->hasLandscapeCompetitionBanner()) {
            return \App\Support\EventImageUrl::placeholder();
        }

        return $this->bannerMobileUrl() ?? $this->cardBannerMediumUrl();
    }

    public function hasLandscapeCompetitionBanner(): bool
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return false;
        }

        $orientation = $this->imageOrientation();

        // Unknown dimensions: keep legacy landscape-style banners working.
        if ($orientation === null) {
            return true;
        }

        return $orientation === \App\Support\ImageDimensions::ORIENTATION_LANDSCAPE;
    }

    /**
     * Detail-page banner source. Landscape fills with cover; accidental
     * portrait/square banners still render (contain+blur) with a warning.
     * Legacy portrait-only images are remapped to poster + placeholder banner.
     */
    public function detailBannerUrl(): string
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return \App\Support\EventImageUrl::placeholder();
        }

        return $this->image_url;
    }

    public function detailBannerMediumUrl(): string
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return \App\Support\EventImageUrl::placeholder();
        }

        return $this->bannerMediumUrl() ?? $this->image_url;
    }

    public function detailBannerMobileUrl(): string
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return \App\Support\EventImageUrl::placeholder();
        }

        return $this->bannerMobileUrl() ?? $this->detailBannerMediumUrl();
    }

    public function detailBannerNeedsContainLayout(): bool
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return false;
        }

        return $this->bannerNeedsContainLayout();
    }

    public function detailBannerOrientation(): ?string
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return \App\Support\ImageDimensions::ORIENTATION_LANDSCAPE;
        }

        return $this->imageOrientation();
    }

    public function shouldWarnNonLandscapeBanner(): bool
    {
        if (! $this->has_uploaded_image || $this->isLegacyPortraitAsBanner()) {
            return false;
        }

        $orientation = $this->imageOrientation();

        return $orientation === \App\Support\ImageDimensions::ORIENTATION_PORTRAIT
            || $orientation === \App\Support\ImageDimensions::ORIENTATION_SQUARE;
    }

    /**
     * Old competitions that only uploaded a portrait into image_path.
     */
    public function isLegacyPortraitAsBanner(): bool
    {
        return $this->has_uploaded_image
            && $this->isPortraitBannerImage()
            && ! filled($this->poster_path);
    }

    public function thumbnailUrl(): string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        if ($this->hasLandscapeCompetitionBanner()) {
            return $this->bannerThumbUrl() ?? $this->image_url;
        }

        return \App\Support\EventImageUrl::placeholder();
    }

    /**
     * Optional 9:16 promotional poster — separate from the 16:9 competition banner.
     * Includes legacy remapping when the only uploaded image was portrait.
     */
    public function competitionPosterUrl(): ?string
    {
        if (filled($this->poster_path)) {
            if (str_starts_with($this->poster_path, 'http://') || str_starts_with($this->poster_path, 'https://')) {
                return $this->poster_path;
            }

            return Storage::disk('public')->url($this->poster_path);
        }

        if ($this->isLegacyPortraitAsBanner()) {
            return $this->image_url;
        }

        return null;
    }

    public function hasCompetitionPoster(): bool
    {
        return filled($this->poster_path) || $this->isLegacyPortraitAsBanner();
    }

    public function hasExplicitCompetitionPoster(): bool
    {
        return filled($this->poster_path);
    }

    public function performanceDurationLabel(): string
    {
        $minutes = (int) ($this->max_performance_duration_minutes ?? 0);

        if ($minutes <= 0) {
            return '—';
        }

        return $minutes === 1 ? '1 Minute' : "{$minutes} Minutes";
    }

    public function votingMethodLabel(): string
    {
        $method = $this->voting_method ?? TalentVotingMethod::StudentOnly;

        if ($method === TalentVotingMethod::JudgesAndStudents
            && $this->judge_percentage !== null
            && $this->student_vote_percentage !== null) {
            return sprintf(
                '%s (%d%% Judges / %d%% Students)',
                $method->label(),
                $this->judge_percentage,
                $this->student_vote_percentage,
            );
        }

        return $method->label();
    }

    public function contestantLimitLabel(): string
    {
        return $this->max_contestants ? (string) $this->max_contestants : 'Unlimited';
    }

    public function performanceDurationPreset(): string
    {
        $minutes = (int) ($this->max_performance_duration_minutes ?? 5);

        return in_array($minutes, [3, 5, 10], true) ? (string) $minutes : 'custom';
    }

    public function winnersCountPreset(): string
    {
        $count = (int) ($this->number_of_winners ?? 3);

        return in_array($count, [1, 2, 3, 5], true) ? (string) $count : 'custom';
    }

    /**
     * @return array<int, string>
     */
    public function acceptedVideoFormatsArray(): array
    {
        return collect(explode(',', (string) ($this->accepted_video_formats ?: 'mp4,mov,webm')))
            ->map(fn ($format) => strtolower(trim($format)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function maxUploadSizeMb(): int
    {
        return (int) ($this->max_upload_size_mb ?: 100);
    }

    public function maxVideoDurationSeconds(): int
    {
        return (int) ($this->max_video_duration_seconds ?: 300);
    }

    public function maxVideoDurationLabel(): string
    {
        $seconds = $this->maxVideoDurationSeconds();
        $minutes = intdiv($seconds, 60);
        $remainder = $seconds % 60;

        if ($minutes > 0 && $remainder === 0) {
            return $minutes === 1 ? '1 minute' : "{$minutes} minutes";
        }

        if ($minutes > 0) {
            return "{$minutes}m {$remainder}s";
        }

        return "{$seconds}s";
    }

    public function isRegistrationOpen(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        if (! $this->published_to_students || $this->isArchived()) {
            return false;
        }

        $method = $this->registration_method ?? TalentRegistrationMethod::Both;

        if (! $method->allowsStudentRegistration()) {
            return false;
        }

        // Student registration visibility uses the registration window only.
        // submission_deadline governs video/upload cutoffs, not list visibility.
        if (! $this->registration_starts_at && ! $this->registration_ends_at) {
            return false;
        }

        if ($this->registration_starts_at && $at->lt($this->registration_starts_at)) {
            return false;
        }

        if ($this->registration_ends_at && $at->gt($this->registration_ends_at)) {
            return false;
        }

        return true;
    }

    public function registrationWindowLabel(): string
    {
        if (! $this->registration_starts_at && ! $this->registration_ends_at) {
            return 'Not scheduled';
        }

        $start = $this->registration_starts_at?->format('M d, Y g:i A') ?? '—';
        $end = $this->registration_ends_at?->format('M d, Y g:i A') ?? '—';

        return "{$start} – {$end}";
    }

    public function votingWindowLabel(): string
    {
        if (! $this->voting_starts_at && ! $this->voting_ends_at) {
            return 'Not scheduled';
        }

        $start = $this->voting_starts_at?->format('M d, Y g:i A') ?? '—';
        $end = $this->voting_ends_at?->format('M d, Y g:i A') ?? '—';

        return "{$start} – {$end}";
    }

    public function registrationMethodLabel(): string
    {
        return ($this->registration_method ?? TalentRegistrationMethod::Both)->label();
    }

    public function submissionMethodLabel(): string
    {
        return ($this->submission_method ?? TalentSubmissionMethod::Both)->label();
    }

    public function rankingMethodLabel(): string
    {
        return ($this->ranking_method ?? TalentRankingMethod::Votes)->label();
    }

    public function isArchived(): bool
    {
        return $this->status === TalentEventStatus::Completed;
    }

    public function hasPublishedResults(): bool
    {
        return $this->status === TalentEventStatus::ResultsPublished
            || $this->results_published_at !== null;
    }

    /**
     * Student-facing card phase: competition status + student progress overlays.
     *
     * @return array{badge: string, cta: string, href: string, phase: string}
     */
    public function studentCardPhase(bool $hasVoted = false, ?string $entryStatus = null): array
    {
        $showHref = route('student.talent-voting.show', $this);
        $detailsHref = route('student.talent-registration.show', $this);
        $myEntriesHref = route('student.talent-registration.my-entries');
        $resolved = $this->currentStatus();

        if ($resolved['key'] === 'results_published') {
            return [
                'phase' => 'results_published',
                'badge' => 'Results Published',
                'cta' => 'View Results',
                'href' => $showHref,
            ];
        }

        if ($hasVoted) {
            return [
                'phase' => 'you_have_voted',
                'badge' => 'You Have Voted',
                'cta' => 'View Event',
                'href' => $showHref,
            ];
        }

        if ($resolved['key'] === 'voting_open') {
            return [
                'phase' => 'voting_open',
                'badge' => 'Voting Open',
                'cta' => 'Vote Now',
                'href' => $showHref,
            ];
        }

        if ($resolved['key'] === 'voting_closed' || $resolved['key'] === 'voting_paused') {
            return [
                'phase' => 'voting_closed',
                'badge' => $resolved['label'],
                'cta' => 'View Event',
                'href' => $showHref,
            ];
        }

        if ($entryStatus === TalentEventEntry::STATUS_PENDING) {
            return [
                'phase' => 'pending_approval',
                'badge' => 'Pending Approval',
                'cta' => 'View Entry',
                'href' => $myEntriesHref,
            ];
        }

        if ($entryStatus === TalentEventEntry::STATUS_APPROVED || $entryStatus === TalentEventEntry::STATUS_REJECTED) {
            return [
                'phase' => 'entry_'.$entryStatus,
                'badge' => $entryStatus === TalentEventEntry::STATUS_APPROVED ? 'Entry Approved' : 'Entry Rejected',
                'cta' => 'View Entry',
                'href' => $myEntriesHref,
            ];
        }

        if ($resolved['key'] === 'registration_open') {
            return [
                'phase' => 'registration_open',
                'badge' => 'Registration Open',
                'cta' => 'View',
                'href' => $detailsHref,
            ];
        }

        if ($resolved['key'] === 'registration_closed') {
            return [
                'phase' => 'registration_closed',
                'badge' => 'Registration Closed',
                'cta' => 'View Event',
                'href' => $showHref,
            ];
        }

        if ($this->registration_starts_at && now()->lt($this->registration_starts_at)) {
            return [
                'phase' => 'registration_opens_soon',
                'badge' => 'Registration Opens Soon',
                'cta' => 'View Event',
                'href' => $showHref,
            ];
        }

        if ($this->isBeforeVotingStart()) {
            return [
                'phase' => 'waiting_for_voting',
                'badge' => 'Waiting for Voting',
                'cta' => 'View Event',
                'href' => $showHref,
            ];
        }

        return [
            'phase' => $resolved['key'],
            'badge' => $resolved['label'],
            'cta' => 'View Event',
            'href' => $showHref,
        ];
    }
}
