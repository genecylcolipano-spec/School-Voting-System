<?php

namespace Tests\Feature\Talent;

use App\Enums\DonationStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventEntryView;
use App\Models\TalentEventVote;
use App\Models\TalentVoteOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TalentPaidSupportVotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config([
            'services.paymongo.secret_key' => 'sk_test_testkey',
            'services.paymongo.webhook_secret' => 'whsk_test_secret',
            'services.paymongo.min_amount' => 20,
            'services.paymongo.webhook_tolerance' => 300,
        ]);
    }

    public function test_existing_competitions_stay_on_one_free_vote(): void
    {
        $student = User::factory()->create();
        $event = $this->makeOpenCompetition(['paid_support_enabled' => false]);
        $entry = $this->makeEntry($event, 'Free Act', withVideo: false);

        $this->assertFalse($event->usesPaidSupport());

        $this->actingAs($student)
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('success');

        $this->actingAs($student)
            ->from(route('student.talent-voting.show', $event))
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('error', 'You have already voted in this talent event.');

        $this->assertSame(1, $event->votes()->count());
    }

    public function test_paid_competition_rejects_the_free_vote_endpoint(): void
    {
        $student = User::factory()->create();
        $event = $this->makePaidCompetition();
        $entry = $this->makeEntry($event, 'Paid Act', withVideo: false);

        $this->actingAs($student)
            ->from(route('student.talent-voting.show', $event))
            ->post(route('student.talent-voting.vote', $entry))
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('error', 'Support this contestant with a paid QR vote.');

        $this->assertSame(0, TalentEventVote::query()->count());
    }

    public function test_three_support_votes_cost_sixty_and_stay_pending_until_paid(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response($this->checkoutSessionPayload('cs_test_talent'), 200),
        ]);

        $student = User::factory()->create();
        $event = $this->makePaidCompetition();
        $entry = $this->makeEntry($event, 'Ana', withVideo: false);

        $this->actingAs($student)
            ->post(route('student.talent-voting.support', $entry), ['quantity' => 3])
            ->assertRedirect('https://checkout.paymongo.com/cs_test_talent');

        $order = TalentVoteOrder::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(3, $order->quantity);
        $this->assertSame(60.0, (float) $order->amount);
        $this->assertSame(DonationStatus::Pending, $order->status);
        $this->assertSame(0, $event->votes()->count());
        $this->assertSame(0.0, (float) $event->fresh()->support_amount_raised);
    }

    public function test_signed_webhook_credits_votes_once(): void
    {
        $student = User::factory()->create();
        $event = $this->makePaidCompetition();
        $entry = $this->makeEntry($event, 'Ana', withVideo: false);
        $order = TalentVoteOrder::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'quantity' => 3,
            'unit_price' => 20,
            'amount' => 60,
            'status' => DonationStatus::Pending,
            'payment_method' => 'qrph',
            'paymongo_checkout_session_id' => 'cs_test_talent_paid',
        ]);

        $payload = $this->paidWebhookPayload('cs_test_talent_paid', $order->id, 6000);
        $raw = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'HTTP_PAYMONGO_SIGNATURE' => $this->signatureHeader($raw),
                'CONTENT_TYPE' => 'application/json',
            ],
            $raw,
        )->assertOk();

        $this->assertTrue($order->fresh()->votesWereCredited());
        $this->assertSame(3, $event->votes()->count());
        $this->assertSame(60.0, (float) $event->fresh()->support_amount_raised);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'HTTP_PAYMONGO_SIGNATURE' => $this->signatureHeader($raw),
                'CONTENT_TYPE' => 'application/json',
            ],
            $raw,
        )->assertOk();

        $this->assertSame(3, $event->votes()->count());
        $this->assertSame(60.0, (float) $event->fresh()->support_amount_raised);
    }

    public function test_student_can_support_self_and_another_contestant(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response($this->checkoutSessionPayload('cs_test_self'), 200),
        ]);

        $student = User::factory()->create();
        $event = $this->makePaidCompetition();
        $mine = $this->makeEntry($event, $student->name, withVideo: false, user: $student);
        $other = $this->makeEntry($event, 'Ben', withVideo: false);

        $this->actingAs($student)
            ->post(route('student.talent-voting.support', $mine), ['quantity' => 1])
            ->assertRedirect();

        $this->actingAs($student)
            ->post(route('student.talent-voting.support', $other), ['quantity' => 2])
            ->assertRedirect();

        $this->assertSame(2, TalentVoteOrder::query()->where('user_id', $student->id)->count());
    }

    public function test_video_entry_still_requires_watch_before_paid_support(): void
    {
        $student = User::factory()->create();
        $event = $this->makePaidCompetition();
        $entry = $this->makeEntry($event, 'Video Act', withVideo: true);

        $this->actingAs($student)
            ->from(route('student.talent-voting.show', $event))
            ->post(route('student.talent-voting.support', $entry), ['quantity' => 1])
            ->assertRedirect(route('student.talent-voting.show', $event))
            ->assertSessionHas('error', 'Watch the performance before supporting this entry.');

        TalentEventEntryView::query()->create([
            'user_id' => $student->id,
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'watched_at' => now(),
        ]);

        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response($this->checkoutSessionPayload('cs_after_watch'), 200),
        ]);

        $this->actingAs($student)
            ->post(route('student.talent-voting.support', $entry), ['quantity' => 1])
            ->assertRedirect('https://checkout.paymongo.com/cs_after_watch');
    }

    public function test_closing_voting_cancels_unpaid_orders_without_crediting_votes(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/*' => Http::response(['data' => ['id' => 'cs_expired']], 200),
        ]);

        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $event = $this->makePaidCompetition(['created_by' => $admin->id]);
        $entry = $this->makeEntry($event, 'Ana', withVideo: false);
        $order = TalentVoteOrder::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'quantity' => 2,
            'unit_price' => 20,
            'amount' => 40,
            'status' => DonationStatus::Pending,
            'payment_method' => 'qrph',
            'paymongo_checkout_session_id' => 'cs_pending_close',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.show', $event))
            ->post(route('admin.talent-competition.close-voting', $event))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(DonationStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(0, $event->votes()->count());
        $this->assertFalse($event->fresh()->isAcceptingVotes());
    }

    public function test_delete_is_blocked_while_paid_support_voting_is_open_and_allowed_when_only_pending(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = User::factory()->create();
        $event = $this->makePaidCompetition(['created_by' => $admin->id]);
        $entry = $this->makeEntry($event, 'Ana', withVideo: false);

        TalentVoteOrder::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'quantity' => 1,
            'unit_price' => 20,
            'amount' => 20,
            'status' => DonationStatus::Paid,
            'payment_method' => 'qrph',
            'paid_at' => now(),
            'votes_credited_at' => now(),
        ]);
        $event->forceFill(['support_amount_raised' => 20])->save();

        $this->actingAs($admin)
            ->from(route('admin.talent-competition.index'))
            ->delete(route('admin.talent-competition.destroy', $event))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse($event->fresh()->trashed());

        $pendingEvent = $this->makePaidCompetition([
            'created_by' => $admin->id,
            'title' => 'Pending Only',
            'status' => TalentEventStatus::Scheduled,
            'published_to_students' => false,
            'voting_starts_at' => now()->addDays(2),
            'voting_ends_at' => now()->addDays(4),
        ]);
        $pendingEntry = $this->makeEntry($pendingEvent, 'Ben', withVideo: false);
        TalentVoteOrder::query()->create([
            'talent_event_id' => $pendingEvent->id,
            'talent_event_entry_id' => $pendingEntry->id,
            'user_id' => $student->id,
            'quantity' => 1,
            'unit_price' => 20,
            'amount' => 20,
            'status' => DonationStatus::Pending,
            'payment_method' => 'qrph',
            'paymongo_checkout_session_id' => 'cs_pending_delete',
        ]);

        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/*' => Http::response(['data' => ['id' => 'cs_expired']], 200),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.talent-competition.destroy', $pendingEvent))
            ->assertRedirect(route('admin.talent-competition.index'))
            ->assertSessionHas('success');

        $this->assertTrue($pendingEvent->fresh()->trashed());
    }

    public function test_late_payment_after_close_does_not_credit_votes(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/refunds' => Http::response(['data' => ['id' => 'rfnd_test']], 200),
        ]);

        $student = User::factory()->create();
        $event = $this->makePaidCompetition([
            'voting_ends_at' => now()->subMinute(),
        ]);
        $entry = $this->makeEntry($event, 'Ana', withVideo: false);
        $order = TalentVoteOrder::query()->create([
            'talent_event_id' => $event->id,
            'talent_event_entry_id' => $entry->id,
            'user_id' => $student->id,
            'quantity' => 2,
            'unit_price' => 20,
            'amount' => 40,
            'status' => DonationStatus::Pending,
            'payment_method' => 'qrph',
            'paymongo_checkout_session_id' => 'cs_late',
            'paymongo_payment_id' => 'pay_late',
        ]);

        $payload = $this->paidWebhookPayload('cs_late', $order->id, 4000);
        $raw = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'HTTP_PAYMONGO_SIGNATURE' => $this->signatureHeader($raw),
                'CONTENT_TYPE' => 'application/json',
            ],
            $raw,
        )->assertOk();

        $this->assertSame(0, $event->votes()->count());
        $this->assertSame(DonationStatus::Cancelled, $order->fresh()->status);
        $this->assertNull($order->fresh()->votes_credited_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makeOpenCompetition(array $overrides = []): TalentEvent
    {
        $election = Election::factory()->create();

        return TalentEvent::query()->create(array_merge([
            'election_id' => $election->id,
            'title' => 'Support Showcase',
            'slug' => 'support-showcase-'.uniqid(),
            'event_date' => now()->addDay(),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::VotingOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_starts_at' => now()->subDays(5),
            'registration_ends_at' => now()->subDay(),
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->addDay(),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function makePaidCompetition(array $overrides = []): TalentEvent
    {
        return $this->makeOpenCompetition(array_merge([
            'paid_support_enabled' => true,
            'vote_price' => 20,
            'support_amount_raised' => 0,
        ], $overrides));
    }

    protected function makeEntry(TalentEvent $event, string $name, bool $withVideo, ?User $user = null): TalentEventEntry
    {
        return TalentEventEntry::query()->create([
            'talent_event_id' => $event->id,
            'user_id' => $user?->id,
            'display_name' => $name,
            'performance_title' => $name.' Act',
            'status' => TalentEventEntry::STATUS_APPROVED,
            'source' => TalentEventEntry::SOURCE_ADMIN,
            'video_url' => $withVideo ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkoutSessionPayload(string $id): array
    {
        return [
            'data' => [
                'id' => $id,
                'type' => 'checkout_session',
                'attributes' => [
                    'checkout_url' => 'https://checkout.paymongo.com/'.$id,
                    'status' => 'active',
                    'payments' => [],
                    'metadata' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paidWebhookPayload(string $sessionId, int $orderId, int $amount): array
    {
        return [
            'data' => [
                'id' => 'evt_talent_1',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'livemode' => false,
                    'data' => [
                        'id' => $sessionId,
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'TVS-'.$orderId,
                            'metadata' => [
                                'talent_vote_order_id' => (string) $orderId,
                            ],
                            'payments' => [[
                                'id' => 'pay_test_1',
                                'attributes' => [
                                    'amount' => $amount,
                                    'status' => 'paid',
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function signatureHeader(string $rawPayload): string
    {
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$rawPayload, 'whsk_test_secret');

        return 't='.$timestamp.',te='.$signature.',li=';
    }
}
