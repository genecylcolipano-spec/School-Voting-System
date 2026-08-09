<?php

namespace App\Services\Portal;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementCategory;
use App\Enums\AnnouncementPriority;
use App\Enums\AnnouncementRelatedModule;
use App\Enums\AnnouncementStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AnnouncementView;
use App\Models\Candidate;
use App\Models\Donation;
use App\Models\Election;
use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Mail\AnnouncementPublishedMail;
use App\Models\User;
use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementService
{
    public function __construct(
        protected PortalNotificationService $notifications,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return [
            'categories' => AnnouncementCategory::cases(),
            'priorities' => AnnouncementPriority::cases(),
            'audiences' => AnnouncementAudience::cases(),
            'relatedModules' => AnnouncementRelatedModule::cases(),
            'statuses' => AnnouncementStatus::manualCases(),
            'gradeLevels' => $this->gradeLevelOptions(),
            'sections' => $this->sectionOptions(),
            'elections' => Election::query()->orderByDesc('created_at')->get(['id', 'title']),
            'talentEvents' => TalentEvent::query()->orderByDesc('created_at')->get(['id', 'title']),
            'events' => Event::query()->orderByDesc('created_at')->get(['id', 'title']),
            'fundraisers' => Fundraiser::query()->orderByDesc('created_at')->get(['id', 'title']),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public function payloadFromValidated(array $validated): array
    {
        $isPublished = (bool) ($validated['is_published'] ?? false);
        $status = AnnouncementStatus::tryFrom((string) ($validated['status'] ?? AnnouncementStatus::Draft->value))
            ?? AnnouncementStatus::Draft;

        if ($isPublished && $status === AnnouncementStatus::Draft) {
            $status = AnnouncementStatus::Published;
        }

        if (! $isPublished) {
            $status = AnnouncementStatus::Draft;
        }

        return [
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'] ?? null,
            'category' => $validated['category'] ?? AnnouncementCategory::General->value,
            'priority' => $validated['priority'] ?? AnnouncementPriority::Normal->value,
            'target_audiences' => $this->normalizeAudiences($validated['target_audiences'] ?? []),
            'target_grade_level' => $validated['target_grade_level'] ?? null,
            'target_section' => $validated['target_section'] ?? null,
            'related_module' => $validated['related_module'] ?? AnnouncementRelatedModule::None->value,
            'related_id' => $this->resolveRelatedId($validated),
            'published_at' => $validated['published_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_published' => $isPublished,
            'status' => $status->value,
            'is_pinned' => (bool) ($validated['is_pinned'] ?? false),
            'notify_in_app' => (bool) ($validated['notify_in_app'] ?? true),
            'show_on_dashboard' => (bool) ($validated['show_on_dashboard'] ?? true),
            'pin_to_homepage' => (bool) ($validated['pin_to_homepage'] ?? false),
            'send_email' => (bool) ($validated['send_email'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function dispatchNotificationsIfNeeded(Announcement $announcement, User $actor, bool $force = false): int
    {
        if (! $announcement->isLive()) {
            return 0;
        }

        if (! $announcement->notify_in_app && ! $announcement->send_email) {
            return 0;
        }

        if (! $force && $announcement->notifications_sent_count > 0) {
            return 0;
        }

        $recipients = $this->recipientUsers($announcement);

        if ($recipients->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($recipients as $recipient) {
            $delivered = false;

            if ($announcement->notify_in_app) {
                $this->notifications->notifyUser(
                    $recipient,
                    'New Announcement',
                    $announcement->title,
                    $recipient->isStudent() ? 'student_announcement' : 'admin_announcement',
                    $actor,
                    \App\Enums\NotificationModule::Announcement,
                    $announcement->id,
                    $announcement->id,
                );
                $delivered = true;
            }

            if ($announcement->send_email && filled($recipient->email)) {
                Mail::to($recipient->email)->queue(new AnnouncementPublishedMail(
                    $announcement,
                    (string) $recipient->name,
                    $this->urlForRecipient($announcement, $recipient),
                ));
                $delivered = true;
            }

            if ($delivered) {
                $sent++;
            }
        }

        $announcement->forceFill([
            'notifications_sent_count' => $announcement->notifications_sent_count + $sent,
        ])->save();

        return $sent;
    }

    public function urlForRecipient(Announcement $announcement, User $recipient): string
    {
        if ($recipient->isStudent()) {
            return route('student.announcements.show', $announcement);
        }

        if ($recipient->isFaculty()) {
            return route('faculty.announcements.show', $announcement);
        }

        return route('admin.announcements.preview', $announcement);
    }

    public function recordView(Announcement $announcement, User $user): void
    {
        $created = AnnouncementView::query()->firstOrCreate(
            [
                'announcement_id' => $announcement->id,
                'user_id' => $user->id,
            ],
            ['viewed_at' => now()],
        );

        if ($created->wasRecentlyCreated) {
            $announcement->increment('view_count');
        }

        $announcement->forceFill(['last_viewed_at' => now()])->save();
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    public function storeAttachments(Announcement $announcement, array $files, User $uploader): void
    {
        $allowedMimes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $mime = (string) $file->getMimeType();
            if (! in_array($mime, $allowedMimes, true)) {
                continue;
            }

            $safeName = $this->sanitizeAttachmentName($file->getClientOriginalName());
            $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
            if (! in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                continue;
            }

            $path = $file->storeAs(
                'announcements/attachments',
                Str::uuid()->toString().'.'.$extension,
                'public'
            );

            AnnouncementAttachment::query()->create([
                'announcement_id' => $announcement->id,
                'original_name' => $safeName,
                'path' => $path,
                'mime_type' => $mime,
                'size_bytes' => $file->getSize() ?: 0,
                'uploaded_by' => $uploader->id,
            ]);
        }
    }

    protected function sanitizeAttachmentName(string $name): string
    {
        $name = basename(str_replace(["\0", '/', '\\'], '', $name));
        $name = preg_replace('/[^\w.\- ()\[\]]+/u', '_', $name) ?: 'attachment';
        $name = trim($name, '._ ');

        return Str::limit($name !== '' ? $name : 'attachment', 180, '');
    }

    public function deleteAttachment(AnnouncementAttachment $attachment): void
    {
        $attachment->deleteFile();
        $attachment->delete();
    }

    public function generateDraft(
        string $title,
        string $summary,
        string $body,
        AnnouncementCategory $category,
        AnnouncementRelatedModule $relatedModule,
        ?int $relatedId,
        string $autoSourceType,
        int $autoSourceId,
        User $actor,
        AnnouncementPriority $priority = AnnouncementPriority::Normal,
    ): Announcement {
        return Announcement::query()->create([
            'title' => $title,
            'slug' => SlugGenerator::unique($title, Announcement::class),
            'summary' => $summary,
            'body' => $body,
            'category' => $category,
            'priority' => $priority,
            'target_audiences' => [AnnouncementAudience::AllUsers->value],
            'related_module' => $relatedModule,
            'related_id' => $relatedId,
            'is_published' => false,
            'status' => AnnouncementStatus::Draft,
            'notify_in_app' => true,
            'show_on_dashboard' => true,
            'is_auto_generated' => true,
            'auto_source_type' => $autoSourceType,
            'auto_source_id' => $autoSourceId,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
    }

    public function generateForElectionCreated(Election $election, User $actor): Announcement
    {
        return $this->generateDraft(
            title: 'New Election: '.$election->title,
            summary: 'A new school election has been created and is being prepared.',
            body: "The election \"{$election->title}\" has been created. Stay tuned for voting schedules and candidate announcements.",
            category: AnnouncementCategory::Election,
            relatedModule: AnnouncementRelatedModule::Election,
            relatedId: $election->id,
            autoSourceType: 'election_created',
            autoSourceId: $election->id,
            actor: $actor,
        );
    }

    public function generateForTalentRegistrationOpen(TalentEvent $event, User $actor): Announcement
    {
        return $this->generateDraft(
            title: 'Talent Registration Open: '.$event->title,
            summary: 'Registration is now open for this talent competition.',
            body: "Students may now register for \"{$event->title}\". Submit your entry before registration closes.",
            category: AnnouncementCategory::TalentCompetition,
            relatedModule: AnnouncementRelatedModule::TalentCompetition,
            relatedId: $event->id,
            autoSourceType: 'talent_registration_open',
            autoSourceId: $event->id,
            actor: $actor,
        );
    }

    public function generateForFundraiserStarted(Fundraiser $fundraiser, User $actor): Announcement
    {
        return $this->generateDraft(
            title: 'Fundraising Campaign Started: '.$fundraiser->title,
            summary: 'A new fundraising campaign is now accepting donations.',
            body: "Support \"{$fundraiser->title}\". Goal: ₱".number_format((float) $fundraiser->goal_amount, 2),
            category: AnnouncementCategory::Fundraising,
            relatedModule: AnnouncementRelatedModule::Fundraising,
            relatedId: $fundraiser->id,
            autoSourceType: 'fundraiser_started',
            autoSourceId: $fundraiser->id,
            actor: $actor,
        );
    }

    public function generateForResultsPublished(string $moduleTitle, AnnouncementRelatedModule $module, int $relatedId, User $actor): Announcement
    {
        return $this->generateDraft(
            title: 'Results Published: '.$moduleTitle,
            summary: 'Official results have been released.',
            body: "Official results for \"{$moduleTitle}\" are now available. View the results page for full standings.",
            category: match ($module) {
                AnnouncementRelatedModule::Election => AnnouncementCategory::Election,
                AnnouncementRelatedModule::TalentCompetition => AnnouncementCategory::TalentCompetition,
                default => AnnouncementCategory::General,
            },
            relatedModule: $module,
            relatedId: $relatedId,
            autoSourceType: 'results_published',
            autoSourceId: $relatedId,
            actor: $actor,
            priority: AnnouncementPriority::High,
        );
    }

    public function generateForSchoolEvent(Event $event, User $actor): Announcement
    {
        return $this->generateDraft(
            title: 'New School Event: '.$event->title,
            summary: 'A new school event has been scheduled.',
            body: $event->description ?: "Details for \"{$event->title}\" will be shared soon.",
            category: AnnouncementCategory::SchoolEvent,
            relatedModule: AnnouncementRelatedModule::SchoolEvent,
            relatedId: $event->id,
            autoSourceType: 'school_event_created',
            autoSourceId: $event->id,
            actor: $actor,
        );
    }

    /**
     * @return Collection<int, User>
     */
    public function recipientUsers(Announcement $announcement): Collection
    {
        return $this->recipientQuery($announcement)->get();
    }

    public function recipientQuery(Announcement $announcement): Builder
    {
        $audiences = $this->normalizeAudiences($announcement->target_audiences ?? []);

        if (in_array(AnnouncementAudience::AllUsers->value, $audiences, true)) {
            return User::query()->where('is_active', true);
        }

        return User::query()
            ->where('is_active', true)
            ->where(function (Builder $query) use ($audiences, $announcement) {
                if (in_array(AnnouncementAudience::Students->value, $audiences, true)) {
                    $query->orWhere('role', UserRole::Student);
                }

                if (in_array(AnnouncementAudience::Faculty->value, $audiences, true)) {
                    $query->orWhere('role', UserRole::Faculty);
                }

                if (in_array(AnnouncementAudience::Administrators->value, $audiences, true)) {
                    $query->orWhere('role', UserRole::Admin);
                }

                if (in_array(AnnouncementAudience::SuperAdministrators->value, $audiences, true)) {
                    $query->orWhere('role', UserRole::SuperAdmin);
                }

                if (in_array(AnnouncementAudience::SpecificGrade->value, $audiences, true) && $announcement->target_grade_level) {
                    $query->orWhere(function (Builder $inner) use ($announcement) {
                        $inner->where('role', UserRole::Student)
                            ->where('grade_level', $announcement->target_grade_level);
                    });
                }

                if (in_array(AnnouncementAudience::SpecificSection->value, $audiences, true) && $announcement->target_section) {
                    $query->orWhere(function (Builder $inner) use ($announcement) {
                        $inner->where('role', UserRole::Student)
                            ->where('section', $announcement->target_section);
                    });
                }

                if (in_array(AnnouncementAudience::ElectionCandidates->value, $audiences, true)) {
                    $candidateUserIds = Candidate::query()
                        ->where('is_active', true)
                        ->whereNotNull('user_id')
                        ->pluck('user_id');

                    if ($candidateUserIds->isNotEmpty()) {
                        $query->orWhereIn('id', $candidateUserIds);
                    }
                }

                if (in_array(AnnouncementAudience::TalentParticipants->value, $audiences, true)) {
                    $participantUserIds = TalentEventEntry::query()
                        ->whereNotNull('user_id')
                        ->pluck('user_id');

                    if ($participantUserIds->isNotEmpty()) {
                        $query->orWhereIn('id', $participantUserIds);
                    }
                }

                if (in_array(AnnouncementAudience::FundraisingDonors->value, $audiences, true)) {
                    $donorUserIds = Donation::query()
                        ->whereNotNull('user_id')
                        ->distinct()
                        ->pluck('user_id');

                    if ($donorUserIds->isNotEmpty()) {
                        $query->orWhereIn('id', $donorUserIds);
                    }
                }
            });
    }

    /**
     * @return list<string>
     */
    protected function normalizeAudiences(array $audiences): array
    {
        $values = array_values(array_filter(array_map(
            fn ($value) => is_string($value) ? $value : null,
            $audiences,
        )));

        return $values === [] ? [AnnouncementAudience::AllUsers->value] : $values;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function resolveRelatedId(array $validated): ?int
    {
        $module = AnnouncementRelatedModule::tryFrom((string) ($validated['related_module'] ?? AnnouncementRelatedModule::None->value))
            ?? AnnouncementRelatedModule::None;

        if ($module === AnnouncementRelatedModule::None) {
            return null;
        }

        $id = $validated['related_id'] ?? null;

        return $id ? (int) $id : null;
    }

    /**
     * @return list<string>
     */
    protected function gradeLevelOptions(): array
    {
        return User::query()
            ->whereNotNull('grade_level')
            ->where('grade_level', '!=', '')
            ->distinct()
            ->orderBy('grade_level')
            ->pluck('grade_level')
            ->all();
    }

    /**
     * @return list<string>
     */
    protected function sectionOptions(): array
    {
        return User::query()
            ->whereNotNull('section')
            ->where('section', '!=', '')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->all();
    }
}
