<?php

namespace Database\Seeders;

use App\Enums\ElectionStatus;
use App\Enums\StudentStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentEventType;
use App\Enums\UserRole;
use App\Models\AdminAssignment;
use App\Models\AdminComplaint;
use App\Models\AdminVerificationRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Partylist;
use App\Models\PartylistPoster;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('account_id', 'ADMIN-001')->first();
        $super = User::query()->where('account_id', 'SUPER-001')->first();

        if (! $admin) {
            return;
        }

        $election = Election::query()->firstOrCreate(
            ['slug' => 'student-council-2026'],
            [
                'title' => 'Student Council Election 2026',
                'description' => 'Annual student council election for assigned grade levels.',
                'voting_starts_at' => now()->subDay(),
                'voting_ends_at' => now()->addDays(3),
                'status' => ElectionStatus::Active,
                'created_by' => $super?->id ?? $admin->id,
                'is_paused' => false,
            ],
        );

        AdminAssignment::query()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'election_id' => $election->id,
                'grade_levels' => ['10', '11'],
                'sections' => ['A', 'B'],
                'strands' => null,
                'turnout_target' => 75,
                'assigned_by' => $super?->id,
            ],
        );

        User::query()->where('account_id', '2026-00001')->update([
            'grade_level' => '10',
            'section' => 'A',
            'student_status' => StudentStatus::Enrolled,
            'is_active' => true,
        ]);

        $category = ElectionCategory::query()->firstOrCreate(
            ['election_id' => $election->id, 'slug' => 'president'],
            ['name' => 'President', 'sort_order' => 1, 'max_selections' => 1],
        );

        $candidate = Candidate::query()->firstOrCreate(
            [
                'election_id' => $election->id,
                'display_name' => 'Maria Santos',
            ],
            [
                'election_category_id' => $category->id,
                'position' => 'President',
                'party_or_group' => 'Unity Party',
                'platform' => 'Transparency, student welfare, and inclusive campus activities.',
                'eligibility_status' => 'pending',
                'is_active' => true,
            ],
        );

        AdminVerificationRequest::query()->firstOrCreate(
            [
                'assigned_to' => $admin->id,
                'subject_type' => Candidate::class,
                'subject_id' => $candidate->id,
            ],
            [
                'title' => 'Verify candidate: Maria Santos',
                'status' => 'pending',
                'notes' => 'Awaiting document check.',
            ],
        );

        AdminComplaint::query()->firstOrCreate(
            [
                'assigned_to' => $admin->id,
                'title' => 'Duplicate vote attempt reported',
            ],
            [
                'election_id' => $election->id,
                'description' => 'Student reported seeing a duplicate submission warning.',
                'status' => 'open',
                'priority' => 'normal',
            ],
        );

        $partylist = Partylist::query()->firstOrCreate(
            ['name' => 'Unity Party'],
            [
                'acronym' => 'UP',
                'platform' => 'Student welfare, transparency, and inclusive leadership for all grade levels.',
                'motto' => 'Together We Rise',
                'status' => 'active',
            ],
        );
        $partylist->elections()->syncWithoutDetaching([$election->id]);

        $progressAlliance = Partylist::query()->firstOrCreate(
            ['name' => 'Progress Alliance'],
            [
                'acronym' => 'PA',
                'platform' => 'Innovation in campus governance and accountable student representation.',
                'motto' => 'Forward Always',
                'status' => 'active',
            ],
        );
        $progressAlliance->elections()->syncWithoutDetaching([$election->id]);

        PartylistPoster::query()->firstOrCreate(
            ['partylist_id' => $partylist->id, 'title' => 'Unity Party Campaign Poster 2026'],
            [
                'election_id' => $election->id,
                'description' => 'Official campaign poster for Unity Party student council slate.',
                'status' => PartylistPoster::STATUS_PENDING,
            ],
        );

        $talentEvents = [
            [
                'slug' => 'talent-competition-2026',
                'title' => 'Talent Competition',
                'type' => TalentEventType::TalentCompetition,
                'event_date' => now()->addDays(5),
                'venue' => 'Main Auditorium',
                'status' => TalentEventStatus::EntriesOpen,
            ],
            [
                'slug' => 'debate-finals-2026',
                'title' => 'Debate Finals',
                'type' => TalentEventType::Debate,
                'event_date' => now()->addDays(7),
                'venue' => 'Room 201',
                'status' => TalentEventStatus::Scheduled,
            ],
            [
                'slug' => 'quiz-bowl-2026',
                'title' => 'Quiz Bowl',
                'type' => TalentEventType::Quiz,
                'event_date' => now()->addDays(10),
                'venue' => 'Library Hall',
                'status' => TalentEventStatus::VotingOpen,
                'voting_starts_at' => now()->subDay(),
                'voting_ends_at' => now()->addDays(2),
            ],
        ];

        foreach ($talentEvents as $data) {
            $event = TalentEvent::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'election_id' => $election->id,
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'description' => "School {$data['title']} for assigned grade levels.",
                    'event_date' => $data['event_date'],
                    'venue' => $data['venue'],
                    'status' => $data['status'],
                    'voting_starts_at' => $data['voting_starts_at'] ?? null,
                    'voting_ends_at' => $data['voting_ends_at'] ?? null,
                    'created_by' => $admin->id,
                ],
            );

            if ($data['slug'] === 'talent-competition-2026') {
                TalentEventEntry::query()->firstOrCreate(
                    ['talent_event_id' => $event->id, 'display_name' => 'Ana Reyes'],
                    [
                        'profile_summary' => 'Grade 10 vocalist and guitarist.',
                        'performance_description' => 'Acoustic rendition of an original composition about campus unity.',
                        'status' => TalentEventEntry::STATUS_PENDING,
                    ],
                );
                TalentEventEntry::query()->firstOrCreate(
                    ['talent_event_id' => $event->id, 'display_name' => 'Carlos Mendoza'],
                    [
                        'profile_summary' => 'Grade 11 contemporary dancer.',
                        'performance_description' => 'Interpretive dance piece themed on student leadership.',
                        'status' => TalentEventEntry::STATUS_APPROVED,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ],
                );
            }

            if ($data['slug'] === 'quiz-bowl-2026') {
                TalentEventEntry::query()->firstOrCreate(
                    ['talent_event_id' => $event->id, 'display_name' => 'Team Sigma'],
                    [
                        'profile_summary' => 'Grade 10 quiz team.',
                        'performance_description' => 'Fast-paced general knowledge and science round specialists.',
                        'status' => TalentEventEntry::STATUS_APPROVED,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ],
                );
                TalentEventEntry::query()->firstOrCreate(
                    ['talent_event_id' => $event->id, 'display_name' => 'Team Alpha'],
                    [
                        'profile_summary' => 'Grade 11 quiz team.',
                        'performance_description' => 'History and literature focused quiz bowl contenders.',
                        'status' => TalentEventEntry::STATUS_APPROVED,
                        'reviewed_by' => $admin->id,
                        'reviewed_at' => now(),
                    ],
                );
            }

            if ($event->approvedEntries()->count() > 0) {
                $event->forceFill([
                    'published_to_students' => true,
                    'published_at' => $event->published_at ?? now(),
                ])->save();
            }
        }
    }
}
