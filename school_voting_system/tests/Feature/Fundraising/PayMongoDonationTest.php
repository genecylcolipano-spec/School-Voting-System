<?php

namespace Tests\Feature\Fundraising;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayMongoDonationTest extends TestCase
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

    public function test_qrph_donation_stays_pending_and_redirects_to_paymongo(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response($this->checkoutSessionPayload('cs_test_qrph', 'https://checkout.paymongo.com/cs_test_qrph'), 200),
        ]);

        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($student)
            ->post(route('student.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Qrph->value,
                'message' => 'Go team',
            ])
            ->assertRedirect('https://checkout.paymongo.com/cs_test_qrph');

        $donation = Donation::query()->first();
        $this->assertNotNull($donation);
        $this->assertSame(DonationStatus::Pending, $donation->status);
        $this->assertSame(DonationPaymentMethod::Qrph, $donation->payment_method);
        $this->assertSame('cs_test_qrph', $donation->paymongo_checkout_session_id);
        $this->assertSame(0.0, (float) $fundraiser->fresh()->amount_raised);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->method() === 'POST'
                && ($payload['data']['attributes']['payment_method_types'] ?? []) === ['qrph']
                && ($payload['data']['attributes']['line_items'][0]['amount'] ?? null) === 5000;
        });
    }

    public function test_retired_payment_methods_cannot_be_submitted(): void
    {
        Http::fake();

        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();

        foreach ([
            DonationPaymentMethod::Gcash,
            DonationPaymentMethod::Maya,
            DonationPaymentMethod::BankTransfer,
        ] as $method) {
            $this->actingAs($student)
                ->from(route('student.fundraising.show', $fundraiser))
                ->post(route('student.fundraising.donate', $fundraiser), [
                    'amount' => 50,
                    'payment_method' => $method->value,
                ])
                ->assertRedirect(route('student.fundraising.show', $fundraiser))
                ->assertSessionHasErrors('payment_method');
        }

        Http::assertNothingSent();
        $this->assertSame(0, Donation::query()->count());
    }

    public function test_unpaid_donation_is_not_counted_in_student_totals(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();

        Donation::record($student, $fundraiser, 75, [
            'payment_method' => DonationPaymentMethod::Gcash,
            'status' => DonationStatus::Pending,
        ]);

        $this->assertSame(0.0, (float) $student->donations()->paid()->sum('amount'));
        $this->assertSame(0.0, (float) $fundraiser->fresh()->amount_raised);
    }

    public function test_cash_donation_is_pending_until_admin_confirms(): void
    {
        Http::fake();

        $student = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $fundraiser = $this->createFundraiser($admin);

        $this->actingAs($student)
            ->post(route('student.fundraising.donate', $fundraiser), [
                'amount' => 100,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Http::assertNothingSent();

        $donation = Donation::query()->first();
        $this->assertSame(DonationStatus::Pending, $donation->status);
        $this->assertSame(0.0, (float) $fundraiser->fresh()->amount_raised);

        $this->actingAs($admin)
            ->post(route('admin.fundraisers.donations.confirm', $donation))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($donation->fresh()->isPaid());
        $this->assertSame(100.0, (float) $fundraiser->fresh()->amount_raised);
    }

    public function test_signed_webhook_marks_donation_paid_once(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();
        $donation = Donation::record($student, $fundraiser, 50, [
            'payment_method' => DonationPaymentMethod::Gcash,
            'paymongo_checkout_session_id' => 'cs_test_paid',
        ]);

        $payload = $this->paidWebhookPayload('cs_test_paid', $donation->id, 5000);
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

        $this->assertTrue($donation->fresh()->isPaid());
        $this->assertSame('pay_test_1', $donation->fresh()->paymongo_payment_id);
        $this->assertSame(50.0, (float) $fundraiser->fresh()->amount_raised);

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

        $this->assertSame(50.0, (float) $fundraiser->fresh()->amount_raised);
        $this->assertSame(1, Donation::query()->paid()->count());
    }

    public function test_invalid_webhook_signature_is_rejected(): void
    {
        $payload = json_encode($this->paidWebhookPayload('cs_unknown', 1, 5000), JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            route('webhooks.paymongo'),
            [],
            [],
            [],
            [
                'HTTP_PAYMONGO_SIGNATURE' => 't='.time().',te=deadbeef,li=',
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload,
        )->assertUnauthorized();

        $this->assertSame(0, Donation::query()->paid()->count());
    }

    public function test_return_url_fulfills_paid_checkout_from_paymongo(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions/cs_test_return' => Http::response(
                $this->checkoutSessionPayload('cs_test_return', 'https://checkout.paymongo.com/cs_test_return', paid: true),
                200,
            ),
        ]);

        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();
        $donation = Donation::record($student, $fundraiser, 50, [
            'payment_method' => DonationPaymentMethod::Maya,
            'paymongo_checkout_session_id' => 'cs_test_return',
        ]);

        $this->actingAs($student)
            ->get(route('student.fundraising.donate.return', [
                'fundraiser' => $fundraiser,
                'donation' => $donation->id,
            ]))
            ->assertRedirect(route('student.fundraising.show', $fundraiser))
            ->assertSessionHas('success');

        $this->assertTrue($donation->fresh()->isPaid());
        $this->assertSame(50.0, (float) $fundraiser->fresh()->amount_raised);
    }

    public function test_blank_or_low_campaign_minimum_is_at_least_twenty(): void
    {
        $fundraiser = $this->createFundraiser();

        $fundraiser->forceFill(['min_donation' => null])->save();
        $this->assertSame(20.0, $fundraiser->fresh()->minimumDonationAmount());

        $fundraiser->forceFill(['min_donation' => 1])->save();
        $this->assertSame(20.0, $fundraiser->fresh()->minimumDonationAmount());

        $fundraiser->forceFill(['min_donation' => 50])->save();
        $this->assertSame(50.0, $fundraiser->fresh()->minimumDonationAmount());
    }

    public function test_donate_form_prefills_minimum_and_labels_paymongo_submit(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();
        $fundraiser->forceFill(['min_donation' => 50])->save();

        $html = $this->actingAs($student)
            ->get(route('student.fundraising.show', $fundraiser))
            ->assertOk()
            ->assertSee('Continue to payment', false)
            ->assertDontSee('>Submit donation<', false)
            ->assertSee('QR Ph')
            ->assertSee('Cash')
            ->assertDontSee('GCash')
            ->assertDontSee('Maya')
            ->assertDontSee('Bank transfer')
            ->assertSee('Campus Drive')
            ->assertSee('Raised')
            ->assertSee('Goal ₱5,000.00')
            ->assertSee('border-cyan-500/50', false)
            ->getContent();

        $this->assertStringContainsString('break-words', $html);
        $this->assertStringNotContainsString('truncate text-2xl', $html);
        $this->assertStringContainsString('h-3 overflow-hidden rounded-full bg-slate-800', $html);

        $this->assertMatchesRegularExpression(
            '/name="amount"[^>]*value="50(\.0+)?"/',
            $html,
        );
    }

    public function test_cash_only_campaign_uses_submit_donation_label(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();
        $fundraiser->forceFill([
            'accept_gcash' => false,
            'accept_maya' => false,
            'accept_qrph' => false,
            'accept_bank_transfer' => false,
            'accept_cash' => true,
        ])->save();

        $this->actingAs($student)
            ->get(route('student.fundraising.show', $fundraiser))
            ->assertOk()
            ->assertSee('Submit donation', false)
            ->assertDontSee('>Continue to payment<', false);
    }

    public function test_donation_below_minimum_is_rejected(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($student)
            ->from(route('student.fundraising.show', $fundraiser))
            ->post(route('student.fundraising.donate', $fundraiser), [
                'amount' => 10,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertRedirect(route('student.fundraising.show', $fundraiser))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, Donation::query()->count());
    }

    public function test_unaccepted_payment_method_is_rejected(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();
        $fundraiser->forceFill(['accept_cash' => false])->save();

        $this->actingAs($student)
            ->from(route('student.fundraising.show', $fundraiser))
            ->post(route('student.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertRedirect(route('student.fundraising.show', $fundraiser))
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Donation::query()->count());
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkoutSessionPayload(string $id, string $url, bool $paid = false): array
    {
        $payments = $paid ? [[
            'id' => 'pay_test_1',
            'attributes' => [
                'amount' => 5000,
                'status' => 'paid',
            ],
        ]] : [];

        return [
            'data' => [
                'id' => $id,
                'type' => 'checkout_session',
                'attributes' => [
                    'checkout_url' => $url,
                    'status' => 'active',
                    'payments' => $payments,
                    'metadata' => [],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paidWebhookPayload(string $sessionId, int $donationId, int $amount): array
    {
        return [
            'data' => [
                'id' => 'evt_test_1',
                'type' => 'event',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'livemode' => false,
                    'data' => [
                        'id' => $sessionId,
                        'type' => 'checkout_session',
                        'attributes' => [
                            'reference_number' => 'DON-'.$donationId,
                            'metadata' => [
                                'donation_id' => (string) $donationId,
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

    protected function createFundraiser(?User $admin = null): Fundraiser
    {
        $admin ??= User::factory()->admin()->create();

        return Fundraiser::query()->create([
            'title' => 'Campus Drive',
            'slug' => 'campus-drive-'.Str::random(6),
            'goal_amount' => 5000,
            'amount_raised' => 0,
            'min_donation' => 20,
            'status' => FundraiserStatus::Active,
            'visibility' => FundraiserVisibility::Public,
            'accept_donations' => true,
            'accept_gcash' => true,
            'accept_maya' => true,
            'accept_qrph' => true,
            'accept_cash' => true,
            'accept_bank_transfer' => true,
            'allow_anonymous' => true,
            'starts_on' => now()->subDay()->toDateString(),
            'ends_on' => now()->addDays(10)->toDateString(),
            'created_by' => $admin->id,
        ]);
    }
}
