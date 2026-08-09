<?php

namespace App\Http\Requests\Admin\Candidate;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\ElectionCategory;
use Illuminate\Validation\Rule;

abstract class CandidateFormRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('election_category_id')) {
            return;
        }

        $electionId = ElectionCategory::query()
            ->whereKey($this->input('election_category_id'))
            ->value('election_id');

        if ($electionId !== null) {
            $this->merge(['election_id' => $electionId]);
        }
    }

    protected function candidateRules(): array
    {
        return [
            'election_id' => ['required', 'exists:elections,id'],
            'election_category_id' => [
                'required',
                Rule::exists('election_categories', 'id')->where(
                    fn ($query) => $query->where('election_id', $this->input('election_id')),
                ),
            ],
            'user_id' => ['nullable', 'exists:users,id'],
            'display_name' => ['required', 'string', 'max:255'],
            'partylist_id' => [
                'nullable',
                'integer',
                Rule::exists('election_partylist', 'partylist_id')->where(
                    fn ($query) => $query->where('election_id', $this->input('election_id')),
                ),
            ],
            'party_or_group' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string'],
            'biography' => ['nullable', 'string'],
            'campaign_promises' => ['nullable', 'string'],
            'grade_level' => ['nullable', 'string', 'max:20'],
            'section' => ['nullable', 'string', 'max:50'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
