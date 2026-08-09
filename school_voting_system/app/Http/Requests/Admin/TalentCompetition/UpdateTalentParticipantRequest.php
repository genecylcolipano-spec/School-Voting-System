<?php

namespace App\Http\Requests\Admin\TalentCompetition;

use App\Enums\TalentCategory;
use App\Enums\TalentSubmissionMethod;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\TalentEventEntry;
use App\Services\Talent\VideoInspectionService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateTalentParticipantRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        if (! $this->scope()->canCreateTalentEvents($this->user())) {
            return false;
        }

        $entry = $this->route('entry');

        if (! $entry instanceof TalentEventEntry) {
            return false;
        }

        $this->scope()->assertTalentEntryInScope($this->user(), $entry);

        return true;
    }

    public function rules(): array
    {
        /** @var TalentEventEntry $entry */
        $entry = $this->route('entry');
        $event = $entry->talentEvent;
        $formats = $event
            ? implode(',', $event->acceptedVideoFormatsArray())
            : 'mp4,mov,webm';
        $maxKilobytes = $event
            ? $event->maxUploadSizeMb() * 1024
            : 102400;

        return [
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
            'video' => ['nullable', 'file', "mimes:{$formats}", "max:{$maxKilobytes}"],
            'video_url' => ['nullable', 'url', 'max:255'],
            'remove_video' => ['nullable', 'boolean'],
            'clear_video_url' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var TalentEventEntry $entry */
            $entry = $this->route('entry');
            $event = $entry->talentEvent;

            if (! $event) {
                return;
            }

            $studentId = trim((string) $this->input('student_id_number'));

            if ($studentId !== '') {
                $duplicate = TalentEventEntry::query()
                    ->where('talent_event_id', $event->id)
                    ->whereKeyNot($entry->id)
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
            $willHaveUpload = $this->hasFile('video')
                || ($entry->video_path && ! $this->boolean('remove_video'));
            $willHaveUrl = filled($this->input('video_url')) && ! $this->boolean('clear_video_url');

            if ($submission === TalentSubmissionMethod::Upload && ! $willHaveUpload) {
                $validator->errors()->add('video', 'This competition requires an uploaded video.');
            }

            if ($submission === TalentSubmissionMethod::Url && ! $willHaveUrl) {
                $validator->errors()->add('video_url', 'This competition requires a video URL.');
            }
        });
    }
}
