<?php

namespace Tests\Feature\Faculty;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class FacultyFundraisingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_faculty_can_browse_and_open_an_active_campaign(): void
    {
        $faculty = User::factory()->faculty()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertSee('Support a Campaign')
            ->assertSee(route('faculty.fundraising.index'), false)
            ->assertSee('Fundraising');

        $this->actingAs($faculty)
            ->get(route('faculty.fundraising.index'))
            ->assertOk()
            ->assertSee($fundraiser->title)
            ->assertSee('Donate')
            ->assertDontSee('Create Fundraiser');

        $this->actingAs($faculty)
            ->get(route('faculty.fundraising.show', $fundraiser))
            ->assertOk()
            ->assertSee('Make a donation')
            ->assertSee(route('faculty.fundraising.donate', $fundraiser), false);
    }

    public function test_faculty_can_submit_a_cash_donation(): void
    {
        $faculty = User::factory()->faculty()->create(['name' => 'Prof Donor']);
        $admin = User::factory()->superAdmin()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($faculty)
            ->from(route('faculty.fundraising.show', $fundraiser))
            ->post(route('faculty.fundraising.donate', $fundraiser), [
                'amount' => 75,
                'payment_method' => DonationPaymentMethod::Cash->value,
                'message' => 'From faculty',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $donation = Donation::query()->first();
        $this->assertNotNull($donation);
        $this->assertSame($faculty->id, $donation->user_id);
        $this->assertSame(DonationStatus::Pending, $donation->status);
        $this->assertSame(0.0, (float) $fundraiser->fresh()->amount_raised);

        $this->actingAs($admin)
            ->get(route('admin.fundraisers.donations'))
            ->assertOk()
            ->assertSee('Prof Donor')
            ->assertSee('Faculty');
    }

    public function test_faculty_qrph_checkout_returns_to_faculty_portal(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response($this->checkoutSessionPayload(), 200),
        ]);

        config([
            'services.paymongo.secret_key' => 'sk_test_testkey',
            'services.paymongo.min_amount' => 20,
        ]);

        $faculty = User::factory()->faculty()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($faculty)
            ->post(route('faculty.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Qrph->value,
            ])
            ->assertRedirect('https://checkout.paymongo.com/cs_test_faculty');

        Http::assertSent(function ($request) use ($fundraiser) {
            $payload = $request->data();
            $success = $payload['data']['attributes']['success_url'] ?? '';
            $cancel = $payload['data']['attributes']['cancel_url'] ?? '';

            return str_contains($success, '/faculty/fundraising/'.$fundraiser->slug.'/donate/return')
                && str_contains($cancel, '/faculty/fundraising/'.$fundraiser->slug.'/donate/cancel');
        });
    }

    public function test_admin_cannot_donate_from_faculty_or_student_portals(): void
    {
        $admin = User::factory()->admin()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($admin)
            ->post(route('faculty.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('student.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertForbidden();

        $this->assertSame(0, Donation::query()->count());
    }

    public function test_student_cannot_open_faculty_fundraising_routes(): void
    {
        $student = User::factory()->create();
        $fundraiser = $this->createFundraiser();

        $this->actingAs($student)
            ->get(route('faculty.fundraising.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('faculty.fundraising.donate', $fundraiser), [
                'amount' => 50,
                'payment_method' => DonationPaymentMethod::Cash->value,
            ])
            ->assertForbidden();
    }

    public function test_hidden_campaign_is_not_listed_for_faculty(): void
    {
        $faculty = User::factory()->faculty()->create();
        $hidden = $this->createFundraiser([
            'title' => 'Hidden Drive',
            'visibility' => FundraiserVisibility::Hidden,
            'status' => FundraiserStatus::Draft,
        ]);

        $this->actingAs($faculty)
            ->get(route('faculty.fundraising.index'))
            ->assertOk()
            ->assertDontSee('Hidden Drive');

        $this->actingAs($faculty)
            ->get(route('faculty.fundraising.show', $hidden))
            ->assertNotFound();
    }

    public function test_disabled_module_hides_faculty_fundraising_nav(): void
    {
        SystemSetting::setValue('enable_fundraising', false, 'boolean');

        $faculty = User::factory()->faculty()->create();

        $this->actingAs($faculty)
            ->get(route('faculty.dashboard'))
            ->assertOk()
            ->assertDontSee(route('faculty.fundraising.index'), false)
            ->assertDontSee('Support a Campaign');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createFundraiser(array $overrides = []): Fundraiser
    {
        $admin = User::factory()->admin()->create();

        return Fundraiser::query()->create(array_merge([
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
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    protected function checkoutSessionPayload(): array
    {
        return [
            'data' => [
                'id' => 'cs_test_faculty',
                'type' => 'checkout_session',
                'attributes' => [
                    'checkout_url' => 'https://checkout.paymongo.com/cs_test_faculty',
                    'status' => 'active',
                    'payments' => [],
                    'metadata' => [],
                ],
            ],
        ];
    }
}
