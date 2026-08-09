<?php

namespace App\Enums;

enum AnnouncementAudience: string
{
    case AllUsers = 'all_users';
    case Students = 'students';
    case Faculty = 'faculty';
    case Administrators = 'administrators';
    case SuperAdministrators = 'super_administrators';
    case ElectionCandidates = 'election_candidates';
    case TalentParticipants = 'talent_participants';
    case FundraisingDonors = 'fundraising_donors';
    case SpecificGrade = 'specific_grade';
    case SpecificSection = 'specific_section';

    public function label(): string
    {
        return match ($this) {
            self::AllUsers => 'All Users',
            self::Students => 'Students',
            self::Faculty => 'Faculty',
            self::Administrators => 'Administrators',
            self::SuperAdministrators => 'Super Administrator',
            self::ElectionCandidates => 'Election Candidates',
            self::TalentParticipants => 'Talent Competition Participants',
            self::FundraisingDonors => 'Fundraising Donors',
            self::SpecificGrade => 'Specific Grade Level',
            self::SpecificSection => 'Specific Section',
        };
    }
}
