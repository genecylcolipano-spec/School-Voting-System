<?php

namespace App\Http\Requests\Admin\TalentCompetition;

use App\Enums\TalentCategory;
use App\Enums\TalentEventType;
use App\Enums\TalentRankingMethod;
use App\Enums\TalentRegistrationMethod;
use App\Enums\TalentSubmissionMethod;
use App\Enums\TalentVotingMethod;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreTalentCompetitionRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        return $this->scope()->canCreateTalentEvents($this->user())
            && $this->scope()->assignedElection($this->user()) !== null;
    }

    public function rules(): array
    {
        return $this->talentRules();
    }

    protected function talentRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'competition_code' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::enum(TalentEventType::class)],
            'talent_category' => ['required', Rule::enum(TalentCategory::class)],
            'organizer' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:voting_starts_at'],
            'venue' => ['required', 'string', 'max:255'],
            'registration_starts_at' => ['nullable', 'date'],
            'registration_ends_at' => ['nullable', 'date', 'after_or_equal:registration_starts_at'],
            'submission_deadline' => ['nullable', 'date', 'after_or_equal:registration_starts_at'],
            'voting_starts_at' => ['required', 'date'],
            'voting_ends_at' => ['required', 'date', 'after:voting_starts_at'],
            'results_publish_at' => ['nullable', 'date', 'after_or_equal:voting_ends_at'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'poster' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'performance_duration' => ['required', Rule::in(['3', '5', '10', 'custom'])],
            'performance_duration_custom' => ['required_if:performance_duration,custom', 'nullable', 'integer', 'min:1', 'max:180'],
            'max_contestants' => ['nullable', 'integer', 'min:1', 'max:500'],
            'max_video_duration_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'max_upload_size_mb' => ['required', 'integer', 'min:1', 'max:1024'],
            'accepted_video_formats' => ['required', 'array', 'min:1'],
            'accepted_video_formats.*' => ['string', Rule::in(['mp4', 'mov', 'webm', 'mkv', 'avi'])],
            'registration_method' => ['required', Rule::enum(TalentRegistrationMethod::class)],
            'submission_method' => ['required', Rule::enum(TalentSubmissionMethod::class)],
            'voting_method' => ['required', Rule::enum(TalentVotingMethod::class)],
            'ranking_method' => ['required', Rule::enum(TalentRankingMethod::class)],
            'judge_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'student_vote_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'winners_count' => ['required', Rule::in(['1', '2', '3', '5', 'custom'])],
            'winners_count_custom' => ['required_if:winners_count,custom', 'nullable', 'integer', 'min:1', 'max:50'],
            'auto_status_updates' => ['nullable', 'boolean'],
            'published_to_students' => ['nullable', 'boolean'],
            // Participants optional — managed via Participants module.
            'participants' => ['nullable', 'array'],
            'participants.*.id' => ['nullable', 'integer', 'exists:talent_event_entries,id'],
            'participants.*.display_name' => ['required_with:participants', 'string', 'max:255'],
            'participants.*.student_id_number' => ['nullable', 'string', 'max:50'],
            'participants.*.grade_level' => ['required_with:participants', 'string', 'max:10'],
            'participants.*.section' => ['required_with:participants', 'string', 'max:20'],
            'participants.*.course_strand' => ['nullable', 'string', 'max:120'],
            'participants.*.talent_category' => ['nullable', Rule::enum(TalentCategory::class)],
            'participants.*.performance_title' => ['nullable', 'string', 'max:200'],
            'participants.*.profile_summary' => ['nullable', 'string', 'max:500'],
            'participants.*.performance_description' => ['nullable', 'string', 'max:1000'],
            'participants.*.video_url' => ['nullable', 'url', 'max:255'],
            'participants.*.social_media' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $method = $this->input('voting_method');

            if ($method === TalentVotingMethod::JudgesAndStudents->value) {
                $judge = $this->input('judge_percentage');
                $student = $this->input('student_vote_percentage');

                if ($judge === null || $student === null) {
                    $validator->errors()->add('judge_percentage', 'Judge and student vote percentages are required for hybrid voting.');

                    return;
                }

                if (((int) $judge + (int) $student) !== 100) {
                    $validator->errors()->add('judge_percentage', 'Judge and student vote percentages must total 100%.');
                    $validator->errors()->add('student_vote_percentage', 'Judge and student vote percentages must total 100%.');
                }
            }

            $maxContestants = $this->input('max_contestants');
            $participants = $this->input('participants', []);

            if ($maxContestants !== null && $maxContestants !== '' && is_array($participants) && count($participants) > 0) {
                if (count($participants) > (int) $maxContestants) {
                    $validator->errors()->add('participants', "You may only register up to {$maxContestants} contestants for this event.");
                }
            }

            $studentIds = [];
            foreach ($participants as $index => $participant) {
                $studentId = trim((string) ($participant['student_id_number'] ?? ''));

                if ($studentId === '') {
                    continue;
                }

                $key = strtolower($studentId);

                if (isset($studentIds[$key])) {
                    $validator->errors()->add("participants.{$index}.student_id_number", "Duplicate Student ID \"{$studentId}\" in this event.");
                } else {
                    $studentIds[$key] = true;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Competition title is required.',
            'event_date.after_or_equal' => 'Event date cannot be earlier than the voting open schedule.',
            'voting_ends_at.after' => 'Voting close must be later than voting open.',
            'performance_duration_custom.required_if' => 'Enter a custom performance duration in minutes.',
            'winners_count_custom.required_if' => 'Enter the custom number of winners.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function competitionSettings(): array
    {
        $duration = $this->input('performance_duration') === 'custom'
            ? (int) $this->input('performance_duration_custom')
            : (int) $this->input('performance_duration');

        $winners = $this->input('winners_count') === 'custom'
            ? (int) $this->input('winners_count_custom')
            : (int) $this->input('winners_count');

        $method = TalentVotingMethod::from($this->input('voting_method'));

        return [
            'competition_code' => filled($this->input('competition_code')) ? trim((string) $this->input('competition_code')) : null,
            'organizer' => filled($this->input('organizer')) ? trim((string) $this->input('organizer')) : null,
            'talent_category' => $this->input('talent_category'),
            'max_performance_duration_minutes' => max(1, $duration),
            'max_contestants' => filled($this->input('max_contestants')) ? (int) $this->input('max_contestants') : null,
            'voting_method' => $method->value,
            'judge_percentage' => $method->requiresHybridPercentages() ? (int) $this->input('judge_percentage') : null,
            'student_vote_percentage' => $method->requiresHybridPercentages() ? (int) $this->input('student_vote_percentage') : null,
            'number_of_winners' => max(1, $winners),
            'ranking_method' => $this->input('ranking_method', TalentRankingMethod::Votes->value),
            'registration_starts_at' => $this->input('registration_starts_at') ?: null,
            'registration_ends_at' => $this->input('registration_ends_at') ?: null,
            'submission_deadline' => $this->input('submission_deadline') ?: null,
            'results_publish_at' => $this->input('results_publish_at') ?: null,
            'registration_method' => $this->input('registration_method', TalentRegistrationMethod::Both->value),
            'submission_method' => $this->input('submission_method', TalentSubmissionMethod::Both->value),
            'max_video_duration_seconds' => max(10, (int) $this->input('max_video_duration_minutes') * 60),
            'max_upload_size_mb' => max(1, (int) $this->input('max_upload_size_mb')),
            'accepted_video_formats' => implode(',', (array) $this->input('accepted_video_formats', ['mp4'])),
            'auto_status_updates' => $this->boolean('auto_status_updates', true),
        ];
    }
}
