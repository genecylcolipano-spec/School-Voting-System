<?php

namespace App\Enums;

enum FundraiserCategory: string
{
    case SchoolImprovement = 'school_improvement';
    case MedicalAssistance = 'medical_assistance';
    case Scholarship = 'scholarship';
    case CommunityOutreach = 'community_outreach';
    case Library = 'library';
    case LaboratoryEquipment = 'laboratory_equipment';
    case Sports = 'sports';
    case DisasterRelief = 'disaster_relief';
    case Others = 'others';

    public function label(): string
    {
        return match ($this) {
            self::SchoolImprovement => 'School Improvement',
            self::MedicalAssistance => 'Medical Assistance',
            self::Scholarship => 'Scholarship',
            self::CommunityOutreach => 'Community Outreach',
            self::Library => 'Library',
            self::LaboratoryEquipment => 'Laboratory Equipment',
            self::Sports => 'Sports',
            self::DisasterRelief => 'Disaster Relief',
            self::Others => 'Others',
        };
    }
}
