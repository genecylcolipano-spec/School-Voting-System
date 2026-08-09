<?php

namespace App\Http\Requests\Student;

use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Talent\StudentTalentRegistrationFlowService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmTalentEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function event(): TalentEvent
    {
        return $this->route('talentEvent');
    }

    public function rules(): array
    {
        return [
            'confirm' => ['accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $event = $this->event();
            $flow = app(StudentTalentRegistrationFlowService::class);
            $user = $this->user();

            if (! $user) {
                $validator->errors()->add('confirm', 'You must be signed in to submit an entry.');

                return;
            }

            $action = $flow->registrationAction($event, $user);

            if (! $action['can_register']) {
                $validator->errors()->add('confirm', match ($action['state']) {
                    'already_registered' => 'You have already submitted an entry for this competition.',
                    'closed' => 'Registration for this competition is closed.',
                    'finished' => 'This competition has finished.',
                    'not_eligible' => 'You are not eligible to register for this competition.',
                    'slots_full' => 'This competition has reached its maximum number of participants.',
                    default => 'Registration is not available for this competition.',
                });
            }

            if (! $flow->getDraft($event, $user)) {
                $validator->errors()->add('confirm', 'Please complete the registration form and review your entry before submitting.');
            }

            if ($user) {
                $alreadyEntered = TalentEventEntry::query()
                    ->where('talent_event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($alreadyEntered) {
                    $validator->errors()->add('confirm', 'You have already submitted an entry for this competition.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'confirm.accepted' => 'Please confirm that you want to submit your competition entry.',
        ];
    }
}
