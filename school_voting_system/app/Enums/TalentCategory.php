<?php

namespace App\Enums;

enum TalentCategory: string
{
    case SoloSinging = 'solo_singing';
    case DuetSinging = 'duet_singing';
    case GroupSinging = 'group_singing_choir';
    case SoloDance = 'solo_dance';
    case GroupDance = 'group_dance';
    case Instrumental = 'instrumental_performance';
    case SpokenPoetry = 'spoken_poetry';
    case DramaActing = 'drama_acting';
    case PublicSpeaking = 'public_speaking';
    case StandUpComedy = 'stand_up_comedy';
    case MagicPerformance = 'magic_performance';
    case LiveArt = 'live_art';
    case ShortFilm = 'short_film';
    case MultimediaPerformance = 'multimedia_performance';
    case BandPerformance = 'band_performance';
    case OpenTalent = 'open_talent';

    public function label(): string
    {
        return match ($this) {
            self::SoloSinging => 'Solo Singing',
            self::DuetSinging => 'Duet Singing',
            self::GroupSinging => 'Group Singing / Choir',
            self::SoloDance => 'Solo Dance',
            self::GroupDance => 'Group Dance',
            self::Instrumental => 'Instrumental Performance',
            self::SpokenPoetry => 'Spoken Poetry',
            self::DramaActing => 'Drama / Acting',
            self::PublicSpeaking => 'Public Speaking',
            self::StandUpComedy => 'Stand-up Comedy',
            self::MagicPerformance => 'Magic Performance',
            self::LiveArt => 'Live Art',
            self::ShortFilm => 'Short Film',
            self::MultimediaPerformance => 'Multimedia Performance',
            self::BandPerformance => 'Band Performance',
            self::OpenTalent => 'Open Talent',
        };
    }
}
