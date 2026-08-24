<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementCategory;
use App\Enums\AnnouncementPriority;
use App\Enums\AnnouncementRelatedModule;
use App\Enums\AnnouncementStatus;
use App\Enums\UserRole;
use App\Support\EventImageUrl;
use App\Support\ImageDimensions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'body',
        'category',
        'priority',
        'target_audiences',
        'target_grade_level',
        'target_section',
        'related_module',
        'related_id',
        'banner_path',
        'banner_variants',
        'published_at',
        'expires_at',
        'is_published',
        'status',
        'is_pinned',
        'notify_in_app',
        'show_on_dashboard',
        'pin_to_homepage',
        'send_email',
        'is_auto_generated',
        'auto_source_type',
        'auto_source_id',
        'view_count',
        'notifications_sent_count',
        'last_viewed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => AnnouncementCategory::class,
            'priority' => AnnouncementPriority::class,
            'target_audiences' => 'array',
            'related_module' => AnnouncementRelatedModule::class,
            'banner_variants' => 'array',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_published' => 'boolean',
            'status' => AnnouncementStatus::class,
            'is_pinned' => 'boolean',
            'notify_in_app' => 'boolean',
            'show_on_dashboard' => 'boolean',
            'pin_to_homepage' => 'boolean',
            'send_email' => 'boolean',
            'is_auto_generated' => 'boolean',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            return static::query()->whereKey((int) $value)->first();
        }

        return static::query()->where('slug', $value)->first();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(AnnouncementView::class);
    }

    /**
     * @return list<AnnouncementAudience>
     */
    public function audienceEnums(): array
    {
        $values = $this->target_audiences ?? [AnnouncementAudience::AllUsers->value];

        return array_values(array_filter(array_map(
            fn (string $value) => AnnouncementAudience::tryFrom($value),
            $values,
        )));
    }

    public function resolvedStatus(?Carbon $on = null): AnnouncementStatus
    {
        $on ??= now();

        if ($this->status === AnnouncementStatus::Archived) {
            return AnnouncementStatus::Archived;
        }

        if ($this->status === AnnouncementStatus::Draft && ! $this->is_published) {
            return AnnouncementStatus::Draft;
        }

        if ($this->expires_at && $this->expires_at->lte($on)) {
            return AnnouncementStatus::Expired;
        }

        if ($this->published_at && $this->published_at->gt($on)) {
            return AnnouncementStatus::Scheduled;
        }

        if ($this->is_published) {
            return AnnouncementStatus::Published;
        }

        return AnnouncementStatus::Draft;
    }

    public function displayStatusLabel(): string
    {
        return $this->resolvedStatus()->label();
    }

    public function isLive(?Carbon $on = null): bool
    {
        return $this->resolvedStatus($on) === AnnouncementStatus::Published;
    }

    public function hasUploadedBanner(): bool
    {
        return filled($this->banner_path);
    }

    public function bannerUrl(): string
    {
        if (! $this->banner_path) {
            return EventImageUrl::placeholder();
        }

        return EventImageUrl::resolve($this->banner_path);
    }

    public function bannerMediumUrl(): ?string
    {
        return $this->bannerVariantUrl('medium_path');
    }

    public function bannerMobileUrl(): ?string
    {
        return $this->bannerVariantUrl('mobile_path');
    }

    public function bannerVariantUrl(string $key): ?string
    {
        $path = ($this->banner_variants ?? [])[$key] ?? null;

        return $path ? EventImageUrl::resolve($path) : null;
    }

    public function bannerOrientation(): ?string
    {
        $variants = $this->banner_variants ?? [];

        if (isset($variants['orientation']) && is_string($variants['orientation'])) {
            return $variants['orientation'];
        }

        return ImageDimensions::orientation($this->bannerDimensions());
    }

    public function bannerNeedsContainLayout(): bool
    {
        return in_array($this->bannerOrientation(), ['portrait', 'square'], true);
    }

    public function bannerDimensions(): ?array
    {
        $variants = $this->banner_variants ?? [];

        if (! isset($variants['width'], $variants['height'])) {
            return null;
        }

        return [
            'width' => (int) $variants['width'],
            'height' => (int) $variants['height'],
        ];
    }

    public function relatedRecordTitle(): ?string
    {
        $record = $this->resolveRelatedRecord();

        return $record?->title ?? $record?->name ?? null;
    }

    public function relatedRecordUrl(?UserRole $forRole = null): ?string
    {
        $role = $forRole ?? Auth::user()?->role ?? UserRole::Student;

        return match ($role) {
            UserRole::Admin, UserRole::SuperAdmin => $this->relatedRecordUrlForAdmin(),
            UserRole::Faculty => $this->relatedRecordUrlForFaculty(),
            default => $this->relatedRecordUrlForStudent(),
        };
    }

    protected function relatedRecordUrlForStudent(): ?string
    {
        return match ($this->related_module) {
            AnnouncementRelatedModule::Election => $this->studentElectionUrl(),
            AnnouncementRelatedModule::TalentCompetition => $this->studentTalentUrl(),
            AnnouncementRelatedModule::SchoolEvent => $this->relatedEvent()?->slug
                ? route('student.events.show', $this->relatedEvent())
                : null,
            AnnouncementRelatedModule::Fundraising => $this->relatedFundraiser()?->slug
                ? route('student.fundraising.show', $this->relatedFundraiser())
                : null,
            default => null,
        };
    }

    protected function relatedRecordUrlForAdmin(): ?string
    {
        return match ($this->related_module) {
            AnnouncementRelatedModule::Election => ($election = $this->relatedElection())
                ? route('admin.elections.edit', $election)
                : null,
            AnnouncementRelatedModule::TalentCompetition => ($event = $this->relatedTalentEvent())
                ? route('admin.talent-competition.show', $event)
                : null,
            AnnouncementRelatedModule::SchoolEvent => ($event = $this->relatedEvent())
                ? route('admin.events.edit', $event)
                : null,
            AnnouncementRelatedModule::Fundraising => ($fundraiser = $this->relatedFundraiser())
                ? route('admin.fundraisers.edit', $fundraiser)
                : null,
            default => null,
        };
    }

    protected function relatedRecordUrlForFaculty(): ?string
    {
        return match ($this->related_module) {
            AnnouncementRelatedModule::Election => $this->facultyElectionUrl(),
            AnnouncementRelatedModule::TalentCompetition => $this->facultyTalentUrl(),
            AnnouncementRelatedModule::SchoolEvent => $this->relatedEvent()?->slug
                ? route('faculty.events.show', $this->relatedEvent())
                : null,
            default => null,
        };
    }

    protected function facultyElectionUrl(): ?string
    {
        $election = $this->relatedElection();

        if (! $election?->slug) {
            return null;
        }

        if ($this->auto_source_type === 'results_published' || $election->shouldShowOfficialResultsToStudents()) {
            return route('faculty.results.election.show', $election);
        }

        return route('faculty.elections.show', $election);
    }

    protected function facultyTalentUrl(): ?string
    {
        $event = $this->relatedTalentEvent();

        if (! $event?->slug) {
            return null;
        }

        if ($this->auto_source_type === 'results_published' || $event->hasPublishedResults()) {
            return route('faculty.results.talent.show', $event);
        }

        return route('faculty.judging.show', $event);
    }

    protected function studentElectionUrl(): ?string
    {
        $election = $this->relatedElection();

        if (! $election?->slug) {
            return null;
        }

        if ($this->auto_source_type === 'results_published' || $election->shouldShowOfficialResultsToStudents()) {
            return route('student.results.election.show', $election);
        }

        return route('student.voting.show', $election);
    }

    protected function studentTalentUrl(): ?string
    {
        $event = $this->relatedTalentEvent();

        if (! $event?->slug) {
            return null;
        }

        if ($this->auto_source_type === 'results_published' || $event->hasPublishedResults()) {
            return route('student.results.talent.show', $event);
        }

        if ($event->isRegistrationOpen()) {
            return route('student.talent-registration.show', $event);
        }

        return route('student.talent-voting.show', $event);
    }

    public function relatedElection(): ?Election
    {
        if ($this->related_module !== AnnouncementRelatedModule::Election || ! $this->related_id) {
            return null;
        }

        return Election::query()->find($this->related_id);
    }

    public function relatedTalentEvent(): ?TalentEvent
    {
        if ($this->related_module !== AnnouncementRelatedModule::TalentCompetition || ! $this->related_id) {
            return null;
        }

        return TalentEvent::query()->find($this->related_id);
    }

    public function relatedEvent(): ?Event
    {
        if ($this->related_module !== AnnouncementRelatedModule::SchoolEvent || ! $this->related_id) {
            return null;
        }

        return Event::query()->find($this->related_id);
    }

    public function relatedFundraiser(): ?Fundraiser
    {
        if ($this->related_module !== AnnouncementRelatedModule::Fundraising || ! $this->related_id) {
            return null;
        }

        return Fundraiser::query()->find($this->related_id);
    }

    public function resolveRelatedRecord(): Election|TalentEvent|Event|Fundraiser|null
    {
        return $this->relatedElection()
            ?? $this->relatedTalentEvent()
            ?? $this->relatedEvent()
            ?? $this->relatedFundraiser();
    }

    /**
     * @return array<string, int|float|null>
     */
    public function statistics(): array
    {
        $uniqueViews = $this->relationLoaded('views')
            ? $this->views->count()
            : $this->views()->count();

        return [
            'total_views' => (int) $this->view_count,
            'unique_views' => $uniqueViews,
            'unread_users' => max(0, $this->notifications_sent_count - $uniqueViews),
            'downloads' => (int) ($this->attachments_sum_download_count ?? $this->attachments()->sum('download_count')),
            'notifications_sent' => (int) $this->notifications_sent_count,
            'last_viewed_at' => $this->last_viewed_at,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', AnnouncementStatus::Archived->value);
            })
            ->orderByDesc('is_pinned')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 4 WHEN 'high' THEN 3 WHEN 'normal' THEN 2 ELSE 1 END DESC")
            ->orderByDesc('published_at');
    }

    public function scopeForDashboard(Builder $query): Builder
    {
        return $query->where('show_on_dashboard', true);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->whereJsonContains('target_audiences', AnnouncementAudience::AllUsers->value)
                ->orWhereNull('target_audiences');

            if ($user->isStudent()) {
                $query->orWhere(function (Builder $students) use ($user) {
                    $students->where(function (Builder $audience) {
                        $audience->whereJsonContains('target_audiences', AnnouncementAudience::Students->value)
                            ->orWhereJsonContains('target_audiences', AnnouncementAudience::SpecificGrade->value)
                            ->orWhereJsonContains('target_audiences', AnnouncementAudience::SpecificSection->value);
                    });

                    $students->where(function (Builder $grade) use ($user) {
                        $grade->whereJsonDoesntContain('target_audiences', AnnouncementAudience::SpecificGrade->value)
                            ->orWhere('target_grade_level', $user->grade_level);
                    });

                    $students->where(function (Builder $section) use ($user) {
                        $section->whereJsonDoesntContain('target_audiences', AnnouncementAudience::SpecificSection->value)
                            ->orWhere('target_section', $user->section);
                    });
                });
            }

            if ($user->isFaculty()) {
                $query->orWhereJsonContains('target_audiences', AnnouncementAudience::Faculty->value);
            }

            if ($user->isAdmin()) {
                $query->orWhereJsonContains('target_audiences', AnnouncementAudience::Administrators->value);
            }

            if ($user->isSuperAdmin()) {
                $query->orWhereJsonContains('target_audiences', AnnouncementAudience::SuperAdministrators->value);
            }

            if (Candidate::query()->where('user_id', $user->id)->where('is_active', true)->exists()) {
                $query->orWhere(function (Builder $candidates) use ($user) {
                    $candidates->whereJsonContains('target_audiences', AnnouncementAudience::ElectionCandidates->value)
                        ->where(function (Builder $related) use ($user) {
                            $related->where('related_module', '!=', AnnouncementRelatedModule::Election->value)
                                ->orWhereNull('related_id')
                                ->orWhereIn(
                                    'related_id',
                                    Candidate::query()
                                        ->where('user_id', $user->id)
                                        ->where('is_active', true)
                                        ->select('election_id'),
                                );
                        });
                });
            }

            if (TalentEventEntry::query()->where('user_id', $user->id)->where('status', TalentEventEntry::STATUS_APPROVED)->exists()) {
                $query->orWhere(function (Builder $participants) use ($user) {
                    $participants->whereJsonContains('target_audiences', AnnouncementAudience::TalentParticipants->value)
                        ->where(function (Builder $related) use ($user) {
                            $related->where('related_module', '!=', AnnouncementRelatedModule::TalentCompetition->value)
                                ->orWhereNull('related_id')
                                ->orWhereIn(
                                    'related_id',
                                    TalentEventEntry::query()
                                        ->where('user_id', $user->id)
                                        ->where('status', TalentEventEntry::STATUS_APPROVED)
                                        ->select('talent_event_id'),
                                );
                        });
                });
            }

            if (Donation::query()->paid()->where('user_id', $user->id)->exists()) {
                $query->orWhere(function (Builder $donors) use ($user) {
                    $donors->whereJsonContains('target_audiences', AnnouncementAudience::FundraisingDonors->value)
                        ->where(function (Builder $related) use ($user) {
                            $related->where('related_module', '!=', AnnouncementRelatedModule::Fundraising->value)
                                ->orWhereNull('related_id')
                                ->orWhereIn(
                                    'related_id',
                                    Donation::query()
                                        ->paid()
                                        ->where('user_id', $user->id)
                                        ->select('fundraiser_id'),
                                );
                        });
                });
            }
        });
    }

    public function deleteBannerFiles(): void
    {
        $paths = array_filter([
            $this->banner_path,
            ...array_values(array_filter([
                ($this->banner_variants ?? [])['medium_path'] ?? null,
                ($this->banner_variants ?? [])['mobile_path'] ?? null,
                ($this->banner_variants ?? [])['thumb_path'] ?? null,
            ])),
        ]);

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
