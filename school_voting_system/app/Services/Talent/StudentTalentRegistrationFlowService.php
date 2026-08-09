<?php

namespace App\Services\Talent;

use App\Enums\TalentEventStatus;
use App\Enums\TalentRegistrationMethod;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentTalentRegistrationFlowService
{
    public const DRAFT_SESSION_PREFIX = 'talent_registration_draft.';

    public const SUCCESS_SESSION_PREFIX = 'talent_registration_success.';

    /**
     * @return array{
     *     can_register: bool,
     *     state: string,
     *     label: string,
     *     href: ?string,
     *     disabled: bool,
     *     registered_count: int,
     *     remaining_slots: ?int,
     *     slots_full: bool,
     *     existing: ?TalentEventEntry
     * }
     */
    public function registrationAction(TalentEvent $event, User $student): array
    {
        $existing = TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->where('user_id', $student->id)
            ->first();

        $registeredCount = $this->activeParticipantCount($event);
        $remaining = $this->remainingSlots($event, $registeredCount);
        $slotsFull = $remaining === 0;

        if ($existing) {
            return [
                'can_register' => false,
                'state' => 'already_registered',
                'label' => 'Already Registered',
                'href' => route('student.talent-registration.entry.show', $existing),
                'disabled' => false,
                'registered_count' => $registeredCount,
                'remaining_slots' => $remaining,
                'slots_full' => $slotsFull,
                'existing' => $existing,
            ];
        }

        if ($event->isArchived() || $event->status === TalentEventStatus::Completed) {
            return [
                'can_register' => false,
                'state' => 'finished',
                'label' => 'Competition Finished',
                'href' => null,
                'disabled' => true,
                'registered_count' => $registeredCount,
                'remaining_slots' => $remaining,
                'slots_full' => $slotsFull,
                'existing' => null,
            ];
        }

        if (! $this->isStudentEligible($event, $student)) {
            return [
                'can_register' => false,
                'state' => 'not_eligible',
                'label' => 'Not Eligible',
                'href' => null,
                'disabled' => true,
                'registered_count' => $registeredCount,
                'remaining_slots' => $remaining,
                'slots_full' => $slotsFull,
                'existing' => null,
            ];
        }

        if ($slotsFull) {
            return [
                'can_register' => false,
                'state' => 'slots_full',
                'label' => 'Registration Full',
                'href' => null,
                'disabled' => true,
                'registered_count' => $registeredCount,
                'remaining_slots' => 0,
                'slots_full' => true,
                'existing' => null,
            ];
        }

        if (! $event->isRegistrationOpen()) {
            return [
                'can_register' => false,
                'state' => 'closed',
                'label' => 'Registration Closed',
                'href' => null,
                'disabled' => true,
                'registered_count' => $registeredCount,
                'remaining_slots' => $remaining,
                'slots_full' => $slotsFull,
                'existing' => null,
            ];
        }

        if (! $this->isCompetitionActive($event)) {
            return [
                'can_register' => false,
                'state' => 'inactive',
                'label' => 'Competition Inactive',
                'href' => null,
                'disabled' => true,
                'registered_count' => $registeredCount,
                'remaining_slots' => $remaining,
                'slots_full' => $slotsFull,
                'existing' => null,
            ];
        }

        return [
            'can_register' => true,
            'state' => 'open',
            'label' => 'Register Now',
            'href' => route('student.talent-registration.register', $event),
            'disabled' => false,
            'registered_count' => $registeredCount,
            'remaining_slots' => $remaining,
            'slots_full' => false,
            'existing' => null,
        ];
    }

    public function isStudentEligible(TalentEvent $event, User $student): bool
    {
        if (! $student->isStudent()) {
            return false;
        }

        if (! $event->isPublishedToStudents()) {
            return false;
        }

        $method = $event->registration_method ?? TalentRegistrationMethod::Both;

        return $method->allowsStudentRegistration();
    }

    public function isCompetitionActive(TalentEvent $event): bool
    {
        return $event->isPublishedToStudents()
            && ! $event->isArchived()
            && $event->status !== TalentEventStatus::Completed;
    }

    public function activeParticipantCount(TalentEvent $event): int
    {
        return TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->whereNotIn('status', [
                TalentEventEntry::STATUS_WITHDRAWN,
                TalentEventEntry::STATUS_DISQUALIFIED,
                TalentEventEntry::STATUS_ARCHIVED,
            ])
            ->count();
    }

    public function remainingSlots(TalentEvent $event, ?int $registeredCount = null): ?int
    {
        if ($event->max_contestants === null) {
            return null;
        }

        $count = $registeredCount ?? $this->activeParticipantCount($event);

        return max(0, (int) $event->max_contestants - $count);
    }

    public function hasAvailableSlots(TalentEvent $event): bool
    {
        $remaining = $this->remainingSlots($event);

        return $remaining === null || $remaining > 0;
    }

    public function assertCanAccessRegisterForm(TalentEvent $event, User $student): void
    {
        $action = $this->registrationAction($event, $student);

        if ($action['can_register']) {
            return;
        }

        if ($action['state'] === 'already_registered' && $action['existing']) {
            throw new HttpResponseException(
                redirect()->route('student.talent-registration.entry.show', $action['existing'])
            );
        }

        throw new HttpResponseException(
            redirect()
                ->route('student.talent-registration.show', $event)
                ->with('error', $action['label'].'. Registration is not available right now.')
        );
    }

    public function draftSessionKey(TalentEvent $event): string
    {
        return self::DRAFT_SESSION_PREFIX.$event->id;
    }

    public function successSessionKey(TalentEvent $event): string
    {
        return self::SUCCESS_SESSION_PREFIX.$event->id;
    }

    /**
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public function stashDraft(TalentEvent $event, User $student, array $fields, array $uploadedFiles): array
    {
        $previous = $this->getDraft($event, $student);
        $previousFiles = is_array($previous['files'] ?? null) ? $previous['files'] : [];
        $storedFiles = $previousFiles;
        $base = "talent/drafts/{$student->id}/{$event->id}";

        foreach (['photo', 'thumbnail', 'video'] as $field) {
            /** @var UploadedFile|null $file */
            $file = $uploadedFiles[$field] ?? null;
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            // Replace only the field being re-uploaded; keep other draft media.
            if (! empty($previousFiles[$field]['path'])) {
                Storage::disk($previousFiles[$field]['disk'] ?? 'public')
                    ->delete($previousFiles[$field]['path']);
            }

            $disk = $field === 'video' ? 'local' : 'public';
            $path = $file->store($base, $disk);
            $storedFiles[$field] = [
                'path' => $path,
                'disk' => $disk,
                'name' => $file->getClientOriginalName(),
            ];
        }

        // Switching to a URL submission clears a previously drafted upload.
        if (filled($fields['video_url'] ?? null) && empty($uploadedFiles['video'])) {
            if (! empty($storedFiles['video']['path'])) {
                Storage::disk($storedFiles['video']['disk'] ?? 'local')
                    ->delete($storedFiles['video']['path']);
            }
            unset($storedFiles['video']);
        }

        $draft = [
            'fields' => $fields,
            'files' => $storedFiles,
            'stashed_at' => now()->toIso8601String(),
            'user_id' => $student->id,
            'talent_event_id' => $event->id,
        ];

        session([$this->draftSessionKey($event) => $draft]);

        return $draft;
    }

    public function getDraft(TalentEvent $event, User $student): ?array
    {
        $draft = session($this->draftSessionKey($event));

        if (! is_array($draft)) {
            return null;
        }

        if ((int) ($draft['user_id'] ?? 0) !== (int) $student->id) {
            return null;
        }

        if ((int) ($draft['talent_event_id'] ?? 0) !== (int) $event->id) {
            return null;
        }

        return $draft;
    }

    public function clearDraft(TalentEvent $event, User $student): void
    {
        $this->clearDraftFiles($event, $student);
        session()->forget($this->draftSessionKey($event));
    }

    public function clearDraftFiles(TalentEvent $event, User $student): void
    {
        $draft = session($this->draftSessionKey($event));

        if (is_array($draft) && isset($draft['files']) && is_array($draft['files'])) {
            foreach ($draft['files'] as $fileMeta) {
                if (! is_array($fileMeta) || empty($fileMeta['path'])) {
                    continue;
                }
                Storage::disk($fileMeta['disk'] ?? 'public')->delete($fileMeta['path']);
            }
        }

        $base = "talent/drafts/{$student->id}/{$event->id}";
        Storage::disk('public')->deleteDirectory($base);
        Storage::disk('local')->deleteDirectory($base);
    }

    public function generateEntryNumber(TalentEvent $event): string
    {
        $prefix = $event->competition_code
            ? Str::upper(Str::slug($event->competition_code, ''))
            : 'TC'.$event->id;

        $sequence = TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->lockForUpdate()
            ->count() + 1;

        do {
            $candidate = sprintf('%s-%04d', $prefix, $sequence);
            $exists = TalentEventEntry::query()->where('entry_number', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
    }

    /**
     * Move draft media into permanent storage paths.
     *
     * @param  array<string, mixed>  $draft
     * @return array{photo_path: ?string, thumbnail_path: ?string, video_path: ?string}
     */
    public function promoteDraftFiles(array $draft): array
    {
        $result = [
            'photo_path' => null,
            'thumbnail_path' => null,
            'video_path' => null,
        ];

        $map = [
            'photo' => ['photo_path', 'talent/photos', 'public'],
            'thumbnail' => ['thumbnail_path', 'talent/thumbnails', 'public'],
            'video' => ['video_path', 'talent/videos', 'local'],
        ];

        foreach ($map as $field => [$column, $directory, $targetDisk]) {
            $meta = $draft['files'][$field] ?? null;
            if (! is_array($meta) || empty($meta['path'])) {
                continue;
            }

            $sourceDisk = $meta['disk'] ?? $targetDisk;
            $sourcePath = $meta['path'];

            if (! Storage::disk($sourceDisk)->exists($sourcePath)) {
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $destination = $directory.'/'.Str::uuid().($extension ? '.'.$extension : '');

            if ($sourceDisk === $targetDisk) {
                Storage::disk($targetDisk)->move($sourcePath, $destination);
            } else {
                Storage::disk($targetDisk)->put($destination, Storage::disk($sourceDisk)->get($sourcePath));
                Storage::disk($sourceDisk)->delete($sourcePath);
            }

            $result[$column] = $destination;
        }

        return $result;
    }

    public function reviewStatusLabel(TalentEventEntry $entry): string
    {
        // Review labels kept distinct from raw entry status for the My Entries table.
        return match ($entry->status) {
            TalentEventEntry::STATUS_APPROVED => 'Approved',
            TalentEventEntry::STATUS_REJECTED => 'Rejected',
            TalentEventEntry::STATUS_PENDING => 'Pending Review',
            default => $entry->statusLabel(),
        };
    }
}
