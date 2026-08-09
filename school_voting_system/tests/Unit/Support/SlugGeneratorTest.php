<?php

namespace Tests\Unit\Support;

use App\Enums\TalentEventStatus;
use App\Enums\TalentVotingMethod;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\User;
use App\Support\SlugGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_unique_avoids_soft_deleted_talent_event_slugs(): void
    {
        $election = Election::factory()->create();
        $admin = User::factory()->admin()->create();

        $existing = TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Showcase',
            'slug' => 'showcase',
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'published_to_students' => false,
            'created_by' => $admin->id,
        ]);

        $existing->delete();

        $this->assertSoftDeleted($existing);

        $slug = SlugGenerator::unique('Showcase', TalentEvent::class);

        $this->assertSame('showcase-1', $slug);
    }

    public function test_unique_increments_active_slug_collisions(): void
    {
        $election = Election::factory()->create();
        $admin = User::factory()->admin()->create();

        TalentEvent::query()->create([
            'election_id' => $election->id,
            'title' => 'Showcase',
            'slug' => 'showcase',
            'event_date' => now()->addDay(),
            'status' => TalentEventStatus::Scheduled,
            'voting_method' => TalentVotingMethod::StudentOnly->value,
            'published_to_students' => false,
            'created_by' => $admin->id,
        ]);

        $slug = SlugGenerator::unique('Showcase', TalentEvent::class);

        $this->assertSame('showcase-1', $slug);
    }
}
