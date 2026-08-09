<?php

namespace App\Http\Requests\Student;

use App\Exceptions\VoteIntegrityException;
use App\Models\Candidate;
use App\Models\Election;
use App\Services\Election\BallotSubmissionService;
use App\Services\Election\StudentElectionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitBallotRequest extends FormRequest
{
    /** @var list<Candidate> */
    protected array $candidatesToCast = [];

    public function authorize(): bool
    {
        $user = $this->user();
        $election = $this->election();

        return $user
            && $user->isStudent()
            && $user->is_active
            && $user->canVote()
            && app(StudentElectionService::class)->canAccessBallot($election);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'selections' => ['required', 'array'],
            'selections.*' => ['required', 'integer', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                $this->candidatesToCast = app(BallotSubmissionService::class)->resolveCandidates(
                    $this->election(),
                    $this->user(),
                    $this->input('selections', []),
                );
            } catch (VoteIntegrityException $exception) {
                $validator->errors()->add('selections', $exception->getMessage());
            }
        });
    }

    /**
     * @return list<Candidate>
     */
    public function candidatesToCast(): array
    {
        return $this->candidatesToCast;
    }

    public function election(): Election
    {
        /** @var Election $election */
        $election = $this->route('election');

        return $election;
    }
}
