<?php

namespace App\Http\Requests\Admin\Fundraiser;

use App\Enums\FundraiserCategory;
use App\Enums\FundraiserStatus;
use App\Enums\FundraiserVisibility;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Fundraiser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreFundraiserRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Fundraiser::class) ?? false;
    }

    public function rules(): array
    {
        return $this->fundraiserRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function fundraiserRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['nullable', Rule::enum(FundraiserCategory::class)],
            'beneficiary' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:500'],
            'expected_beneficiaries' => ['nullable', 'string', 'max:255'],
            'goal_amount' => ['required', 'numeric', 'min:1'],
            'min_donation' => ['nullable', 'numeric', 'min:1'],
            'max_donation' => ['nullable', 'numeric', 'min:1'],
            'allow_anonymous' => ['sometimes', 'boolean'],
            'generate_receipt' => ['sometimes', 'boolean'],
            'accept_cash' => ['sometimes', 'boolean'],
            'accept_gcash' => ['sometimes', 'boolean'],
            'accept_maya' => ['sometimes', 'boolean'],
            'accept_bank_transfer' => ['sometimes', 'boolean'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'visibility' => ['required', Rule::enum(FundraiserVisibility::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'accept_donations' => ['sometimes', 'boolean'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::in(array_map(
                fn (FundraiserStatus $status) => $status->value,
                FundraiserStatus::manualCases()
            ))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $goal = (float) $this->input('goal_amount', 0);
            $min = $this->filled('min_donation') ? (float) $this->input('min_donation') : null;
            $max = $this->filled('max_donation') ? (float) $this->input('max_donation') : null;

            if ($min !== null && $goal > 0 && $goal < $min) {
                $validator->errors()->add('goal_amount', 'Goal amount must be greater than or equal to the minimum donation.');
            }

            if ($min !== null && $max !== null && $max < $min) {
                $validator->errors()->add('max_donation', 'Maximum donation must be greater than or equal to the minimum donation.');
            }

            if ($this->filled('starts_on') && $this->filled('ends_on')) {
                if (strtotime((string) $this->input('ends_on')) < strtotime((string) $this->input('starts_on'))) {
                    $validator->errors()->add('ends_on', 'End date cannot be before the start date.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_anonymous' => $this->boolean('allow_anonymous'),
            'generate_receipt' => $this->boolean('generate_receipt'),
            'accept_cash' => $this->boolean('accept_cash'),
            'accept_gcash' => $this->boolean('accept_gcash'),
            'accept_maya' => $this->boolean('accept_maya'),
            'accept_bank_transfer' => $this->boolean('accept_bank_transfer'),
            'is_featured' => $this->boolean('is_featured'),
            'accept_donations' => $this->boolean('accept_donations'),
            'min_donation' => $this->input('min_donation') ?: null,
            'max_donation' => $this->input('max_donation') ?: null,
            'category' => $this->input('category') ?: null,
        ]);
    }
}
