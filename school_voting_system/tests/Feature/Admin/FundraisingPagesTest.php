<?php

namespace Tests\Feature\Admin;

use App\Enums\DonationPaymentMethod;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Models\Donation;
use App\Models\Fundraiser;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FundraisingPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_fundraising_pages_use_unread_notification_count(): void
    {
        $super = User::factory()->superAdmin()->create();
        $campaign = $this->makeCampaign($super, 'Campus Drive');

        PortalNotification::query()->create([
            'title' => 'Fundraising ping',
            'message' => 'Unread for fundraising',
            'type' => 'info',
            'user_id' => $super->id,
            'recipient_role' => 'super_admin',
        ]);

        $this->actingAs($super)
            ->get(route('admin.fundraisers.index'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Every fundraising campaign across the institution.')
            ->assertSee('Campus Drive');

        $this->actingAs($super)
            ->get(route('admin.fundraisers.donations'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->assertSee('Paid gifts across every campaign');

        $this->actingAs($super)
            ->get(route('admin.fundraisers.transactions'))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false);

        $html = $this->actingAs($super)
            ->withSession(['success' => 'Unique fundraising flash'])
            ->get(route('admin.fundraisers.edit', $campaign))
            ->assertOk()
            ->assertSee('data-initial-count="1"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, 'Unique fundraising flash'));
    }

    public function test_super_admin_sees_every_campaign_and_regular_admin_sees_own_only(): void
    {
        $super = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $this->makeCampaign($admin, 'My Campus Drive');
        $this->makeCampaign($other, 'Other Campus Drive');

        $this->actingAs($super)
            ->get(route('admin.fundraisers.index'))
            ->assertOk()
            ->assertSee('My Campus Drive')
            ->assertSee('Other Campus Drive');

        $this->actingAs($admin)
            ->get(route('admin.fundraisers.index'))
            ->assertOk()
            ->assertSee('My Campus Drive')
            ->assertDontSee('Other Campus Drive');

        $this->actingAs($admin)
            ->get(route('admin.fundraisers.donations'))
            ->assertOk()
            ->assertSee('Paid gifts for campaigns you created');
    }

    public function test_empty_campaigns_offer_a_create_link(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('admin.fundraisers.index'))
            ->assertOk()
            ->assertSee('No fundraisers yet.')
            ->assertSee('Create your first campaign')
            ->assertSee(route('admin.fundraisers.create'), false);
    }

    public function test_deleting_a_campaign_confirms_the_fundraiser_was_removed(): void
    {
        $super = User::factory()->superAdmin()->create();
        $campaign = $this->makeCampaign($super, 'Disposable Drive');

        $this->actingAs($super)
            ->from(route('admin.fundraisers.index'))
            ->delete(route('admin.fundraisers.destroy', $campaign))
            ->assertRedirect(route('admin.fundraisers.index'))
            ->assertSessionHas('success', 'Fundraising campaign deleted successfully.');

        $this->assertSoftDeleted($campaign);
    }

    public function test_campaign_form_only_offers_qr_ph_and_cash(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->get(route('admin.fundraisers.create'))
            ->assertOk()
            ->assertSee('Accept Cash')
            ->assertSee('Accept QR Ph')
            ->assertDontSee('Accept GCash')
            ->assertDontSee('Accept Maya')
            ->assertDontSee('Accept Bank Transfer');
    }

    public function test_saving_a_campaign_ignores_retired_payment_methods(): void
    {
        $super = User::factory()->superAdmin()->create();

        $this->actingAs($super)
            ->post(route('admin.fundraisers.store'), [
                'title' => 'QR Cash Drive',
                'goal_amount' => 1000,
                'visibility' => FundraiserVisibility::Public->value,
                'status' => FundraiserStatus::Active->value,
                'accept_cash' => '1',
                'accept_qrph' => '1',
                'accept_gcash' => '1',
                'accept_maya' => '1',
                'accept_bank_transfer' => '1',
            ])
            ->assertRedirect();

        $campaign = Fundraiser::query()->where('title', 'QR Cash Drive')->first();
        $this->assertNotNull($campaign);
        $this->assertTrue($campaign->accept_cash);
        $this->assertTrue($campaign->accept_qrph);
        $this->assertFalse($campaign->accept_gcash);
        $this->assertFalse($campaign->accept_maya);
        $this->assertFalse($campaign->accept_bank_transfer);
    }

    public function test_regular_admin_cannot_confirm_another_admins_donation(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();
        $campaign = $this->makeCampaign($other, 'Other Drive');
        $donation = Donation::record(User::factory()->create(), $campaign, 50, [
            'payment_method' => DonationPaymentMethod::Cash,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.fundraisers.donations.confirm', $donation))
            ->assertForbidden();
    }

    protected function makeCampaign(User $creator, string $title): Fundraiser
    {
        return Fundraiser::query()->create([
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
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
            'created_by' => $creator->id,
        ]);
    }
}
