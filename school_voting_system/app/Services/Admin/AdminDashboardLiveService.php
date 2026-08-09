<?php

namespace App\Services\Admin;

use App\Models\Event;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminDashboardLiveService
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AdminAnalyticsService $analytics,
        protected AdminLiveVotingService $liveVoting,
    ) {}

    public function snapshot(User $admin): array
    {
        $statistics = $this->scope->statistics($admin);
        $voterBreakdown = $this->scope->voterBreakdown($admin);

        return [
            'updated_at' => now()->toIso8601String(),
            'stats' => [
                'turnout_percent' => $statistics['turnout_percent'],
                'votes_cast' => $statistics['votes_cast'],
                'eligible_voters' => $statistics['eligible_voters'],
                'not_voted' => $voterBreakdown['notVoted'],
                'partylists' => $statistics['partylists'],
                'candidates' => $statistics['candidates'],
                'election_status' => $statistics['election_status'],
                'active_fundraisers' => $statistics['active_fundraisers'],
            ],
            'stats_sparklines' => $this->scope->statCardSparklines($admin),
            'voter_breakdown' => $voterBreakdown,
            'analytics' => $this->analytics->dashboardWidgets($admin),
            'voting' => $this->liveVoting->progress($admin),
            'fundraisers' => $this->serializeFundraisers($this->scope->fundraisers($admin)),
            'events_preview' => $this->eventsPreview($admin),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function eventsPreview(User $admin): array
    {
        $canCreateTalent = $this->scope->canCreateTalentEvents($admin);
        $canCreateEvents = $admin->can('create', Event::class);

        return collect()
            ->merge(
                $this->scope->talentEvents($admin)->map(
                    fn (TalentEvent $event) => $this->serializeTalentEvent($event, $canCreateTalent),
                ),
            )
            ->merge(
                $this->scope->schoolEvents($admin)->map(
                    fn (Event $event) => $this->serializeSchoolEvent($event, $admin, $canCreateEvents),
                ),
            )
            ->sortByDesc('sort_ts')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Fundraiser>  $fundraisers
     * @return array<int, array<string, mixed>>
     */
    protected function serializeFundraisers(Collection $fundraisers): array
    {
        return $fundraisers->map(fn (Fundraiser $fundraiser) => [
            'id' => $fundraiser->id,
            'title' => $fundraiser->title,
            'status' => $fundraiser->resolvedStatus()->value,
            'amount_raised' => (float) $fundraiser->amount_raised,
            'goal_amount' => (float) $fundraiser->goal_amount,
            'progress_percent' => $fundraiser->progressPercent(),
            'donations_count' => (int) $fundraiser->donations_count,
            'ends_on' => $fundraiser->ends_on?->format('M d, Y'),
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeTalentEvent(TalentEvent $event, bool $canDelete): array
    {
        return [
            'kind' => 'talent',
            'id' => $event->id,
            'title' => $event->title,
            'category' => $event->type?->label() ?? 'Talent',
            'schedule' => $event->event_date?->format('M d, Y') ?? '—',
            'status' => $event->currentStatusKey(),
            'status_label' => $event->displayStatusLabel(),
            'image_url' => $event->image_url,
            'edit_url' => route('admin.talent-competition.edit', $event),
            'can_delete' => $canDelete,
            'delete_url' => route('admin.talent-competition.destroy', $event),
            'sort_ts' => $event->event_date?->timestamp ?? 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeSchoolEvent(Event $event, User $admin, bool $canManage): array
    {
        return [
            'kind' => 'school',
            'id' => $event->id,
            'title' => $event->title,
            'category' => 'School Event',
            'schedule' => $event->event_date?->format('M d, Y') ?? '—',
            'status' => $event->status?->value ?? 'scheduled',
            'status_label' => null,
            'image_url' => $event->image_url,
            'edit_url' => route('admin.events.edit', $event),
            'can_delete' => $canManage && $admin->can('delete', $event),
            'delete_url' => route('admin.events.destroy', $event),
            'sort_ts' => $event->event_date?->timestamp ?? 0,
        ];
    }
}
