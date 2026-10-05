<?php

namespace App\Http\Requests\Admin\TalentCompetition;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEvent;

class UpdateTalentCompetitionRequest extends StoreTalentCompetitionRequest
{
    public function authorize(): bool
    {
        $talentEvent = $this->route('talentEvent');

        if (! $this->scope()->canCreateTalentEvents($this->user())) {
            return false;
        }

        if (! $talentEvent instanceof TalentEvent) {
            return false;
        }

        $this->scope()->assertTalentEventInScope($this->user(), $talentEvent);

        return true;
    }

    public function rules(): array
    {
        $rules = $this->talentRules();
        // Banner remains optional on edit so existing competitions stay editable.
        $rules['image'] = ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'];

        return $rules;
    }

    public function competitionSettings(): array
    {
        $settings = parent::competitionSettings();
        unset($settings['paid_support_enabled'], $settings['support_amount_raised']);

        $talentEvent = $this->route('talentEvent');
        if ($talentEvent instanceof TalentEvent && $talentEvent->usesPaidSupport()) {
            $settings['vote_price'] = round(max(20, (float) ($this->input('vote_price') ?: $talentEvent->vote_price ?: 20)), 2);
        } else {
            unset($settings['vote_price']);
        }

        return $settings;
    }
}
