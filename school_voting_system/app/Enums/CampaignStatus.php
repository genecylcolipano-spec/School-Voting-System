<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    /**
     * Only Active campaigns may be attached to elections.
     */
    public function isSelectableForElections(): bool
    {
        return $this === self::Active;
    }

    /**
     * Students may browse Active campaigns before, during, and after voting.
     */
    public function isVisibleToStudents(): bool
    {
        return $this === self::Active;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases(),
        );
    }
}
