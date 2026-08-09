<?php

namespace App\Http\Requests\Admin\Announcement;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementCategory;
use App\Enums\AnnouncementPriority;
use App\Enums\AnnouncementRelatedModule;
use App\Enums\AnnouncementStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Announcement;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Announcement::class) ?? false;
    }

    public function rules(): array
    {
        return $this->announcementRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function announcementRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'category' => ['nullable', Rule::enum(AnnouncementCategory::class)],
            'priority' => ['nullable', Rule::enum(AnnouncementPriority::class)],
            'target_audiences' => ['nullable', 'array'],
            'target_audiences.*' => [Rule::enum(AnnouncementAudience::class)],
            'target_grade_level' => ['nullable', 'string', 'max:20'],
            'target_section' => ['nullable', 'string', 'max:50'],
            'related_module' => ['nullable', Rule::enum(AnnouncementRelatedModule::class)],
            'related_id' => ['nullable', 'integer', 'min:1'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
            ],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer', 'exists:announcement_attachments,id'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(array_map(
                fn (AnnouncementStatus $status) => $status->value,
                AnnouncementStatus::manualCases()
            ))],
            'is_pinned' => ['sometimes', 'boolean'],
            'notify_in_app' => ['sometimes', 'boolean'],
            'show_on_dashboard' => ['sometimes', 'boolean'],
            'pin_to_homepage' => ['sometimes', 'boolean'],
            'send_email' => ['sometimes', 'boolean'],
            'notify_students' => ['nullable', 'boolean'],
            'resend_notifications' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('published_at') && $this->filled('expires_at')) {
                if (strtotime((string) $this->input('expires_at')) <= strtotime((string) $this->input('published_at'))) {
                    $validator->errors()->add('expires_at', 'Expiration must be after the publish date.');
                }
            }

            $audiences = $this->input('target_audiences', []);
            if (in_array(AnnouncementAudience::SpecificGrade->value, $audiences, true) && ! $this->filled('target_grade_level')) {
                $validator->errors()->add('target_grade_level', 'Select a grade level for this audience.');
            }
            if (in_array(AnnouncementAudience::SpecificSection->value, $audiences, true) && ! $this->filled('target_section')) {
                $validator->errors()->add('target_section', 'Select a section for this audience.');
            }

            $module = AnnouncementRelatedModule::tryFrom((string) $this->input('related_module', AnnouncementRelatedModule::None->value));
            if ($module && $module !== AnnouncementRelatedModule::None && ! $this->filled('related_id')) {
                $validator->errors()->add('related_id', 'Select a related record for this module.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'is_pinned' => $this->boolean('is_pinned'),
            'notify_in_app' => $this->boolean('notify_in_app'),
            'show_on_dashboard' => $this->boolean('show_on_dashboard'),
            'pin_to_homepage' => $this->boolean('pin_to_homepage'),
            'send_email' => $this->boolean('send_email'),
            'notify_students' => $this->boolean('notify_students'),
            'resend_notifications' => $this->boolean('resend_notifications'),
            'target_audiences' => $this->input('target_audiences', [AnnouncementAudience::AllUsers->value]),
        ]);
    }
}
