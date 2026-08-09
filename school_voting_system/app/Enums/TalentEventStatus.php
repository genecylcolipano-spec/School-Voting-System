<?php

namespace App\Enums;

enum TalentEventStatus: string
{
    case Scheduled = 'scheduled';
    case EntriesOpen = 'entries_open';
    case VotingOpen = 'voting_open';
    case ResultsPublished = 'results_published';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::EntriesOpen => 'Entries Open',
            self::VotingOpen => 'Voting Open',
            self::ResultsPublished => 'Results Published',
            self::Completed => 'Completed',
        };
    }

    public function isVotingOpen(): bool
    {
        return $this === self::VotingOpen;
    }
}
