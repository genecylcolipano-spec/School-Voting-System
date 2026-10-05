<?php

namespace App\Http\Requests\Admin\Election;

use App\Enums\ElectionStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Support\SchoolCourses;
use Illuminate\Validation\Rule;

abstract class ElectionFormRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['positions', 'new_positions', 'existing_positions'] as $key) {
            $rows = $this->input($key);

            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $index => $row) {
                if (! is_array($row) || ! array_key_exists('audience_course', $row)) {
                    continue;
                }

                $course = trim((string) $row['audience_course']);
                $rows[$index]['audience_course'] = $course === '' ? null : strtoupper($course);
            }

            $this->merge([$key => $rows]);
        }
    }

    protected function electionDetailRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'voting_starts_at' => ['nullable', 'date'],
            'voting_ends_at' => ['nullable', 'date', 'after_or_equal:voting_starts_at'],
            'status' => ['required', Rule::enum(ElectionStatus::class)],
        ];
    }

    protected function positionRules(string $prefix): array
    {
        return [
            $prefix => ['nullable', 'array'],
            "{$prefix}.*.name" => ['nullable', 'string', 'max:255'],
            "{$prefix}.*.audience_course" => ['nullable', 'string', Rule::in(SchoolCourses::codes())],
        ];
    }

    protected function participatingCampaignRules(): array
    {
        return [
            'partylists' => ['nullable', 'array'],
            'partylists.*' => ['integer', 'distinct', 'exists:partylists,id'],
        ];
    }

    /**
     * The campaign ids submitted for this election. A candidate may only be
     * assigned to one of these participating campaigns.
     *
     * @return array<int, int>
     */
    protected function submittedPartylistIds(): array
    {
        return collect($this->input('partylists', []))
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function partylistRules(string $prefix): array
    {
        return [
            $prefix => ['nullable', 'array'],
            "{$prefix}.*.name" => ['nullable', 'string', 'max:255'],
            "{$prefix}.*.acronym" => ['nullable', 'string', 'max:50'],
            "{$prefix}.*.platform" => ['nullable', 'string'],
            "{$prefix}.*.motto" => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function candidateRules(string $prefix, bool $useCategoryId): array
    {
        $rules = [
            $prefix => ['nullable', 'array'],
            "{$prefix}.*.display_name" => ['nullable', 'string', 'max:255'],
            "{$prefix}.*.party_or_group" => ['nullable', 'string', 'max:255'],
            "{$prefix}.*.partylist_id" => ['nullable', 'integer', Rule::in($this->submittedPartylistIds())],
            "{$prefix}.*.platform" => ['nullable', 'string'],
            "{$prefix}.*.is_active" => ['nullable', 'boolean'],
            "{$prefix}.*.photo" => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($useCategoryId) {
            $rules["{$prefix}.*.election_category_id"] = ['nullable', 'exists:election_categories,id'];
        } else {
            $rules["{$prefix}.*.position_index"] = ['nullable', 'integer', 'min:0'];
        }

        return $rules;
    }
}
