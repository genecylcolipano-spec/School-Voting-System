@php
    $election = $election ?? null;
    $isEdit = $election !== null;
    $formAction = $isEdit ? route('admin.elections.update', $election) : route('admin.elections.store');
@endphp

<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Manage Election' : 'Create Election'" :user="$user" :notifications-count="$notificationsCount">
        <form
            id="election-setup-form"
            method="POST"
            action="{{ $formAction }}"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf
            @if ($isEdit) @method('PUT') @endif

            {{-- Step 1: Election details --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">1</span>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Election Details</h2>
                        <p class="text-sm text-slate-400">Title, schedule, and status</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        @include('admin.partials.form-input', ['label' => 'Title', 'name' => 'title', 'value' => optional($election)->title, 'required' => true])
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Description</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('description', optional($election)->description) }}</textarea>
                    </div>
                    @include('admin.partials.form-input', ['label' => 'Voting starts', 'name' => 'voting_starts_at', 'type' => 'datetime-local', 'value' => optional(optional($election)->voting_starts_at)->format('Y-m-d\TH:i')])
                    @include('admin.partials.form-input', ['label' => 'Voting ends', 'name' => 'voting_ends_at', 'type' => 'datetime-local', 'value' => optional(optional($election)->voting_ends_at)->format('Y-m-d\TH:i')])
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Status</label>
                        <select name="status" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', optional($election)->status?->value) === $status->value)>{{ ucfirst($status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            {{-- Step 2: Positions --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">2</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Positions</h2>
                            <p class="text-sm text-slate-400">Define offices students will vote for</p>
                        </div>
                    </div>
                    <button type="button" data-add-position class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">+ Add position</button>
                </div>

                @if ($isEdit && $election->categories->isNotEmpty())
                    <div class="mb-4 rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Current positions</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($election->categories as $category)
                                <span class="rounded-full bg-violet-500/15 px-3 py-1 text-xs font-medium text-violet-200">{{ $category->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div id="positions-list" class="space-y-3" data-prefix="{{ $isEdit ? 'new_positions' : 'positions' }}">
                    @if (! $isEdit)
                        <div class="position-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-[1fr_auto]" data-index="0">
                            <input type="text" name="positions[0][name]" placeholder="e.g. President" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            <button type="button" data-remove-row class="rounded-lg border border-rose-500/30 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Remove</button>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Step 3: Participating Campaigns --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">3</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Participating Campaigns</h2>
                            <p class="text-sm text-slate-400">Select which Active campaigns take part in this election</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.campaigns.index') }}" class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">Manage campaigns</a>
                </div>

                @if ($campaigns->isEmpty())
                    <p class="rounded-xl border border-amber-500/20 bg-amber-950/20 px-4 py-3 text-sm text-amber-100">
                        No Active campaigns available.
                        <a href="{{ route('admin.campaigns.create') }}" class="font-semibold text-violet-300 hover:text-violet-200">Create a campaign</a>
                        and set it to Active first.
                    </p>
                @else
                    <div id="participating-campaigns" class="grid gap-2 sm:grid-cols-2">
                        @foreach ($campaigns as $campaign)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-200 hover:border-violet-500/40">
                                <input
                                    type="checkbox"
                                    name="partylists[]"
                                    value="{{ $campaign->id }}"
                                    data-campaign-id="{{ $campaign->id }}"
                                    data-campaign-name="{{ $campaign->name }}"
                                    data-campaign-acronym="{{ $campaign->acronym }}"
                                    @checked(in_array($campaign->id, $selectedPartylistIds ?? [], true))
                                    class="campaign-checkbox rounded border-slate-700 bg-slate-950/50 text-violet-500"
                                />
                                <span class="flex items-center gap-2">
                                    @if ($campaign->color)
                                        <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $campaign->color }}"></span>
                                    @endif
                                    <span class="font-medium text-white">{{ $campaign->name }}</span>
                                    @if ($campaign->acronym)
                                        <span class="text-xs text-violet-300">{{ $campaign->acronym }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('partylists')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                    @error('partylists.*')<p class="mt-2 text-sm text-rose-400">{{ $message }}</p>@enderror
                @endif
            </section>

            {{-- Step 4: Candidates --}}
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white">4</span>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Candidates</h2>
                            <p class="text-sm text-slate-400">Assign candidates to positions</p>
                        </div>
                    </div>
                    <button type="button" data-add-candidate class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-200 hover:bg-violet-500/10">+ Add candidate</button>
                </div>

                @if ($isEdit && $election->candidates->isNotEmpty())
                    <div id="existing-candidates" class="mb-4 space-y-3">
                        @foreach ($election->candidates as $candidate)
                            @php
                                $existingPhotoUrl = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                    ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                    : null;
                            @endphp
                            <div class="candidate-row grid gap-3 rounded-xl border border-slate-800 bg-slate-950/40 p-4 md:grid-cols-2">
                                @include('admin.elections.partials.candidate-photo-field', [
                                    'inputName' => "existing_candidates[{$candidate->id}][photo]",
                                    'removeName' => "existing_candidates[{$candidate->id}][remove_photo]",
                                    'photoUrl' => $existingPhotoUrl,
                                ])
                                <input type="text" name="existing_candidates[{{ $candidate->id }}][display_name]" value="{{ old("existing_candidates.{$candidate->id}.display_name", $candidate->display_name) }}" placeholder="Display name" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                                <select name="existing_candidates[{{ $candidate->id }}][election_category_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                                    @foreach ($election->categories as $category)
                                        <option value="{{ $category->id }}" @selected(old("existing_candidates.{$candidate->id}.election_category_id", $candidate->election_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <select name="existing_candidates[{{ $candidate->id }}][partylist_id]" class="rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                                    <option value="">— Independent (no campaign) —</option>
                                    @foreach ($election->partylists as $campaign)
                                        <option value="{{ $campaign->id }}" @selected(old("existing_candidates.{$candidate->id}.partylist_id", $candidate->partylist_id) == $campaign->id)>{{ $campaign->name }}@if ($campaign->acronym) ({{ $campaign->acronym }})@endif</option>
                                    @endforeach
                                </select>
                                <label class="flex items-center gap-2 text-sm text-slate-300">
                                    <input type="checkbox" name="existing_candidates[{{ $candidate->id }}][is_active]" value="1" @checked(old("existing_candidates.{$candidate->id}.is_active", $candidate->is_active)) class="rounded border-slate-700 bg-slate-950/50 text-violet-500" />
                                    Active
                                </label>
                                <textarea name="existing_candidates[{{ $candidate->id }}][platform]" rows="2" placeholder="Platform" class="md:col-span-2 rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old("existing_candidates.{$candidate->id}.platform", $candidate->platform) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div
                    id="candidates-list"
                    class="space-y-3"
                    data-prefix="{{ $isEdit ? 'new_candidates' : 'candidates' }}"
                    data-is-edit="{{ $isEdit ? '1' : '0' }}"
                    data-categories="{{ $isEdit ? $election->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->toJson() : '[]' }}"
                ></div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-6 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    {{ $isEdit ? 'Save changes' : 'Create election' }}
                </button>
                <a href="{{ route('admin.elections.index') }}" class="rounded-xl border border-slate-700 px-6 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
            </div>
        </form>
    </x-admin-portal>

    @vite(['resources/js/election-form.js'])
</x-app-layout>
