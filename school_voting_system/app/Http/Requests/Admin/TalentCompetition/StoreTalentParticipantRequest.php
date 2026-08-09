<?php

namespace App\Http\Requests\Admin\TalentCompetition;

use App\Enums\TalentCategory;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentSubmissionMethod;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Talent\VideoInspectionService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreTalentParticipantRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canCreateTalentEvents($this->user())) {
            return false;
        }

        if (! filled($this->input('talent_event_id'))) {
            return true;
        }

        $event = TalentEvent::query()->find($this->input('talent_event_id'));

        if (! $event instanceof TalentEvent) {
            return true;
        }

        $this->scope()->assertTalentEventInScope($this->user(), $event);

        $method = $event->registration_method ?? TalentRegistrationMethod::Both;

        return $method->allowsAdminManaged();
    }

    public function rules(): array
    {
        $event = TalentEvent::query()->find($this->input('talent_event_id'));
        $formats = $event instanceof TalentEvent
            ? implode(',', $event->acceptedVideoFormatsArray())
            : 'mp4,mov,webm';
        $maxKilobytes = $event instanceof TalentEvent
            ? $event->maxUploadSizeMb() * 1024
            : 102400;

        $submission = $event?->submission_method ?? TalentSubmissionMethod::Both;
        $videoRules = ['nullable', 'file', "mimes:{$formats}", "max:{$maxKilobytes}"];
        $urlRules = ['nullable', 'url', 'max:255'];

        // Admin may register contestant details first; media is optional at create
        // but must match the competition's allowed submission method when provided.
        if ($submission === TalentSubmissionMethod::Url) {
            // Upload not allowed — ignore file if somehow sent (validated in after()).
        }

        return [
            'talent_event_id' => ['required', 'integer', 'exists:talent_events,id'],
            'display_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['nullable', 'string', 'max:50'],
            'grade_level' => ['required', 'string', 'max:10'],
            'section' => ['required', 'string', 'max:20'],
            'course_strand' => ['nullable', 'string', 'max:120'],
            'talent_category' => ['nullable', Rule::enum(TalentCategory::class)],
            'performance_title' => ['nullable', 'string', 'max:200'],
            'profile_summary' => ['nullable', 'string', 'max:500'],
            'performance_description' => ['nullable', 'string', 'max:1000'],
            'social_media' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'video' => $videoRules,
            'video_url' => $urlRules,
            'approve_immediately' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $event = TalentEvent::query()->find($this->input('talent_event_id'));

            if (! $event instanceof TalentEvent) {
                return;
            }

            if ($event->max_contestants !== null) {
                $count = TalentEventEntry::query()->where('talent_event_id', $event->id)->count();

                if ($count >= (int) $event->max_contestants) {
                    $validator->errors()->add('talent_event_id', "This competition has reached its maximum of {$event->max_contestants} participants.");
                }
            }

            $studentId = trim((string) $this->input('student_id_number'));

            if ($studentId !== '') {
                $duplicate = TalentEventEntry::query()
                    ->where('talent_event_id', $event->id)
                    ->whereRaw('LOWER(student_id_number) = ?', [strtolower($studentId)])
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('student_id_number', 'A participant with this Student ID already exists for this competition.');
                }
            }

            if ($this->hasFile('video')) {
                $file = $this->file('video');
                $duration = $file && $file->isValid()
                    ? app(VideoInspectionService::class)->durationSeconds($file->getRealPath())
                    : null;
                $maxSeconds = $event->maxVideoDurationSeconds();

                if ($duration !== null && $duration > $maxSeconds) {
                    $validator->errors()->add(
                        'video',
                        "Video duration ({$duration}s) exceeds the maximum allowed ({$maxSeconds}s)."
                    );
                }
            }

            $submission = $event->submission_method ?? TalentSubmissionMethod::Both;

            if ($submission === TalentSubmissionMethod::Upload && filled($this->input('video_url')) && ! $this->hasFile('video')) {
                $validator->errors()->add('video', 'This competition only accepts uploaded videos.');
            }

            if ($submission === TalentSubmissionMethod::Url && $this->hasFile('video')) {
                $validator->errors()->add('video', 'This competition only accepts video URLs.');
            }
        });
    }
}
