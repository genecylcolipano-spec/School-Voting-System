<?php

namespace App\Services\Faculty;

use App\Models\Election;
use App\Models\TalentEvent;
use App\Services\Student\StudentResultsService;
use Illuminate\Support\Collection;

class FacultyResultsService
{
    public function __construct(
        protected StudentResultsService $studentResults,
    ) {}

    /**
     * Campus-wide published official results only. Faculty cannot vote.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function listPublished(): Collection
    {
        return $this->studentResults->listEvents()
            ->filter(fn (array $event) => ($event['is_official'] ?? false) === true)
            ->map(function (array $event) {
                $event['can_vote'] = false;
                $event['already_voted'] = false;
                $event['show_url'] = ($event['type'] ?? '') === 'talent'
                    ? route('faculty.results.talent.show', $event['slug'])
                    : route('faculty.results.election.show', $event['slug']);

                return $event;
            })
            ->values();
    }

    public function assertPublishedElection(Election $election): void
    {
        $this->studentResults->assertVisibleElection($election);
        abort_unless($this->studentResults->isElectionOfficial($election), 404);
    }

    public function assertPublishedTalent(TalentEvent $talentEvent): void
    {
        $this->studentResults->assertVisibleTalentEvent($talentEvent);
        abort_unless($this->studentResults->isTalentOfficial($talentEvent), 404);
    }

    /**
     * @return array<string, mixed>
     */
    public function electionDetail(Election $election): array
    {
        return $this->studentResults->electionDetail($election);
    }

    /**
     * @return array<string, mixed>
     */
    public function talentDetail(TalentEvent $talentEvent): array
    {
        return $this->studentResults->talentDetail($talentEvent);
    }
}
