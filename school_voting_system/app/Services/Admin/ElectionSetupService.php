<?php

namespace App\Services\Admin;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionCategory;
use App\Models\Partylist;
use App\Services\Media\ImageCompressionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElectionSetupService
{
    public function __construct(
        protected ImageCompressionService $images,
    ) {}

    public function syncOnCreate(Election $election, array $data): void
    {
        $this->syncPartylists($election, $data['partylists'] ?? []);
        $categoryMap = $this->createPositions($election, $data['positions'] ?? []);
        $this->createCandidates($election, $data['candidates'] ?? [], $categoryMap);
    }

    public function syncOnUpdate(Election $election, array $data): void
    {
        $this->syncPartylists($election, $data['partylists'] ?? []);
        $this->createPositions($election, $data['new_positions'] ?? []);

        foreach ($data['existing_candidates'] ?? [] as $id => $row) {
            if (empty($row['display_name'])) {
                continue;
            }

            $candidate = Candidate::query()
                ->where('election_id', $election->id)
                ->whereKey($id)
                ->first();

            if ($candidate) {
                [$partylistId, $partyLabel] = $this->resolvePartylist($row['partylist_id'] ?? null, $row['party_or_group'] ?? null);

                $attributes = [
                    'election_category_id' => $row['election_category_id'],
                    'display_name' => $row['display_name'],
                    'partylist_id' => $partylistId,
                    'party_or_group' => $partyLabel,
                    'platform' => $row['platform'] ?? null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ];

                $photo = $row['photo'] ?? null;

                if ($photo instanceof UploadedFile) {
                    if ($candidate->photo_path) {
                        Storage::disk('public')->delete($candidate->photo_path);
                    }
                    $attributes['photo_path'] = $this->images->storeOptimized($photo, 'candidate-photos');
                } elseif (! empty($row['remove_photo']) && $candidate->photo_path) {
                    Storage::disk('public')->delete($candidate->photo_path);
                    $attributes['photo_path'] = null;
                }

                $candidate->update($attributes);
            }
        }

        $categoryMap = $election->categories()
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        $this->createCandidates($election, $data['new_candidates'] ?? [], $categoryMap, useCategoryIds: true);
    }

    /**
     * Attach the selected campaigns to the election via the pivot. Only Active
     * campaigns (or those already attached) are allowed; historical links are
     * preserved and no campaign records are duplicated.
     *
     * @param  array<int, mixed>  $submitted
     */
    protected function syncPartylists(Election $election, array $submitted): void
    {
        $attachedIds = $election->partylists()->pluck('partylists.id')->all();
        $activeIds = Partylist::query()->active()->pluck('id')->all();
        $allowed = array_unique(array_merge($activeIds, $attachedIds));

        $ids = array_values(array_intersect(
            array_map('intval', $submitted),
            $allowed,
        ));

        $election->partylists()->sync($ids);
    }

    /**
     * @return array<int, int> position index => category id
     */
    protected function createPositions(Election $election, array $positions): array
    {
        $map = [];
        $sortOrder = $election->categories()->count();

        foreach ($positions as $index => $position) {
            $name = trim((string) ($position['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $category = ElectionCategory::query()->create([
                'election_id' => $election->id,
                'name' => $name,
                'slug' => $this->uniqueCategorySlug($election->id, $name),
                'sort_order' => $sortOrder++,
            ]);

            $map[$index] = $category->id;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $categoryMap
     */
    protected function createCandidates(Election $election, array $candidates, array $categoryMap, bool $useCategoryIds = false): void
    {
        foreach ($candidates as $index => $row) {
            $displayName = trim((string) ($row['display_name'] ?? ''));

            if ($displayName === '') {
                continue;
            }

            $categoryId = $useCategoryIds
                ? ($row['election_category_id'] ?? null)
                : ($categoryMap[$row['position_index'] ?? $index] ?? null);

            if (! $categoryId) {
                continue;
            }

            $photo = $row['photo'] ?? null;
            [$partylistId, $partyLabel] = $this->resolvePartylist($row['partylist_id'] ?? null, $row['party_or_group'] ?? null);

            Candidate::query()->create([
                'election_id' => $election->id,
                'election_category_id' => $categoryId,
                'display_name' => $displayName,
                'partylist_id' => $partylistId,
                'party_or_group' => $partyLabel,
                'platform' => $row['platform'] ?? null,
                'photo_path' => $photo instanceof UploadedFile
                    ? $this->images->storeOptimized($photo, 'candidate-photos')
                    : null,
                'eligibility_status' => 'verified',
                'is_active' => (bool) ($row['is_active'] ?? true),
            ]);
        }
    }

    /**
     * Resolve a candidate's campaign FK plus a denormalized display label.
     * Prefers the selected campaign name; falls back to the legacy free-text.
     *
     * @return array{0: int|null, 1: string|null}
     */
    protected function resolvePartylist(mixed $partylistId, ?string $fallbackLabel): array
    {
        $partylistId = $partylistId !== null && $partylistId !== '' ? (int) $partylistId : null;

        if ($partylistId === null) {
            return [null, $fallbackLabel];
        }

        $name = Partylist::query()->whereKey($partylistId)->value('name');

        return [$partylistId, $name ?? $fallbackLabel];
    }

    protected function uniqueCategorySlug(int $electionId, string $name): string
    {
        $base = Str::slug($name) ?: 'position';
        $slug = $base;
        $counter = 1;

        while (ElectionCategory::query()->where('election_id', $electionId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
