<?php

namespace App\Http\Requests\Student;

use App\Enums\TalentCategory;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Media\ImageCompressionService;
use App\Services\Talent\StudentTalentRegistrationFlowService;
use App\Services\Talent\VideoInspectionService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitTalentEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();
        if ($user === null) {
            return;
        }

        $merged = [];
        foreach ($user->talentRegistrationIdentity() as $key => $value) {
            if ($value !== '') {
                $merged[$key] = $value;
            }
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    protected function event(): TalentEvent
    {
        return $this->route('talentEvent');
    }

    public function rules(): array
    {
        $event = $this->event();
        $formats = implode(',', $event->acceptedVideoFormatsArray());
        $maxKilobytes = $event->maxUploadSizeMb() * 1024;
        $draft = $this->user()
            ? app(StudentTalentRegistrationFlowService::class)->getDraft($event, $this->user())
            : null;
        $hasDraftVideo = is_array($draft) && ! empty($draft['files']['video']['path']);
        $hasDraftVideoUrl = is_array($draft) && filled($draft['fields']['video_url'] ?? null);

        $videoRules = ['nullable', 'file', "mimes:{$formats}", "max:{$maxKilobytes}"];
        $videoUrlRules = ['nullable', 'url', 'max:255'];

        if (! $hasDraftVideo && ! $hasDraftVideoUrl) {
            $videoRules[] = 'required_without:video_url';
            $videoUrlRules[] = 'required_without:video';
        }

        return [
            'display_name' => ['required', 'string', 'max:255'],
            'student_id_number' => ['required', 'string', 'max:50'],
            'grade_level' => ['required', 'string', 'max:10'],
            'section' => ['required', 'string', 'max:20'],
            'course_strand' => ['nullable', 'string', 'max:120'],
            'talent_category' => ['required', Rule::enum(TalentCategory::class)],
            'performance_title' => ['required', 'string', 'max:200'],
            'profile_summary' => ['nullable', 'string', 'max:500'],
            'performance_description' => ['required', 'string', 'max:1000'],
            'social_media' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:'.ImageCompressionService::MAX_UPLOAD_KILOBYTES],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:'.ImageCompressionService::MAX_UPLOAD_KILOBYTES],
            'video' => $videoRules,
            'video_url' => $videoUrlRules,
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $event = $this->event();
            $flow = app(StudentTalentRegistrationFlowService::class);
            $user = $this->user();

            if (! $user) {
                $validator->errors()->add('display_name', 'You must be signed in to register.');

                return;
            }

            $flow->assertCanAccessRegisterForm($event, $user);

            $studentId = trim((string) $this->input('student_id_number'));

            if ($studentId !== '') {
                $duplicate = TalentEventEntry::query()
                    ->where('talent_event_id', $event->id)
                    ->whereRaw('LOWER(student_id_number) = ?', [strtolower($studentId)])
                    ->where(function ($inner) use ($user) {
                        $inner->whereNull('user_id')->orWhere('user_id', '!=', $user->id);
                    })
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('student_id_number', 'A submission with this Student ID already exists for this competition.');
                }
            }

            $file = $this->file('video');
            $draft = app(StudentTalentRegistrationFlowService::class)->getDraft($event, $user);
            $hasVideo = ($file && $file->isValid())
                || filled($this->input('video_url'))
                || (is_array($draft) && (! empty($draft['files']['video']['path']) || filled($draft['fields']['video_url'] ?? null)));

            if (! $hasVideo) {
                $validator->errors()->add('video', 'Upload a performance video or provide a video URL.');
            }

            if ($file && $file->isValid()) {
                $maxSeconds = $event->maxVideoDurationSeconds();
                $duration = app(VideoInspectionService::class)->durationSeconds($file->getRealPath());

                if ($duration !== null && $duration > $maxSeconds) {
                    $validator->errors()->add(
                        'video',
                        "The performance video is {$duration}s long, which exceeds the {$maxSeconds}s ({$event->maxVideoDurationLabel()}) limit."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        $uploadMb = (int) (ImageCompressionService::MAX_UPLOAD_KILOBYTES / 1024);

        return [
            'video.required_without' => 'Upload a performance video or provide a video URL.',
            'video_url.required_without' => 'Provide a video URL or upload a performance video.',
            'video.mimes' => 'The uploaded video format is not accepted for this competition.',
            'video.max' => 'The uploaded video exceeds the maximum allowed size.',
            'photo.image' => 'The profile photo must be a JPG, PNG, or WEBP image.',
            'photo.mimes' => 'The profile photo must be a JPG, PNG, or WEBP image.',
            'photo.max' => "This photo is too large to upload. Choose a JPG, PNG, or WEBP up to {$uploadMb} MB. It will be compressed automatically.",
            'thumbnail.image' => 'The video thumbnail must be a JPG, PNG, or WEBP image.',
            'thumbnail.mimes' => 'The video thumbnail must be a JPG, PNG, or WEBP image.',
            'thumbnail.max' => "This thumbnail is too large to upload. Choose a JPG, PNG, or WEBP up to {$uploadMb} MB. It will be compressed automatically.",
        ];
    }
}
