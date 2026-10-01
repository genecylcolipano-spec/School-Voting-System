<?php

namespace Tests\Feature\Support;

use App\Enums\TalentCategory;
use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoCapitalizeHintsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_global_script_no_longer_rewrites_typed_values(): void
    {
        $appJs = file_get_contents(resource_path('js/app.js'));

        $this->assertIsString($appJs);
        $this->assertStringNotContainsString('auto-capitalize', $appJs);
        $this->assertFileDoesNotExist(resource_path('js/auto-capitalize.js'));
    }

    public function test_campaign_title_fields_do_not_force_word_caps(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.campaigns.create'))
            ->assertOk()
            ->assertSee('name="motto"', false)
            ->assertSee('autocapitalize="none"', false)
            ->assertDontSee('resources/js/auto-capitalize.js', false);
    }

    public function test_student_talent_form_hints_names_only(): void
    {
        $student = User::factory()->create();
        $event = TalentEvent::query()->create([
            'election_id' => Election::factory()->create()->id,
            'title' => 'ROSEMONT IDOL',
            'slug' => 'rosemont-idol-'.uniqid(),
            'event_date' => now()->addDays(10),
            'venue' => 'Auditorium',
            'status' => TalentEventStatus::EntriesOpen,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'registration_method' => TalentRegistrationMethod::Both,
            'talent_category' => TalentCategory::OpenTalent,
            'registration_starts_at' => now()->subHour(),
            'registration_ends_at' => now()->addDays(3),
            'published_to_students' => true,
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $html = $this->actingAs($student)
            ->get(route('student.talent-registration.register', $event))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="display_name"', $html);
        $this->assertMatchesRegularExpression('/name="display_name"[^>]*autocapitalize="words"/', $html);
        $this->assertMatchesRegularExpression('/name="performance_title"[^>]*autocapitalize="none"/', $html);
        $this->assertMatchesRegularExpression('/name="profile_summary"[^>]*autocapitalize="none"/', $html);
        $this->assertMatchesRegularExpression('/name="performance_description"[^>]*autocapitalize="sentences"/', $html);
    }
}
