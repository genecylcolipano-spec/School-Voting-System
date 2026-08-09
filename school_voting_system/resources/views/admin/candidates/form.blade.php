@php
    $candidate = $candidate ?? null;
    $isEdit = $candidate !== null;
    $formAction = $isEdit ? route('admin.candidates.update', $candidate) : route('admin.candidates.store');
@endphp

<x-app-layout>
    <x-admin-portal :title="$isEdit ? 'Edit Candidate' : 'Add Candidate'" :user="$user" :notifications-count="$notificationsCount">
        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="max-w-2xl space-y-4 rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            @if ($elections->isEmpty())
                <div class="mb-4 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                    Create an election before adding candidates.
                    <a href="{{ route('admin.elections.create') }}" class="ml-1 font-semibold text-amber-100 underline">Create election</a>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-300">Election</label>
                <select id="candidate-election-id" name="election_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    @foreach ($elections as $election)
                        <option value="{{ $election->id }}" @selected(old('election_id', optional($candidate)->election_id) == $election->id)>{{ $election->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Category</label>
                <select id="candidate-category-id" name="election_category_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            data-election-id="{{ $category->election_id }}"
                            @selected(old('election_category_id', optional($candidate)->election_category_id) == $category->id)
                        >{{ $category->name }}</option>
                    @endforeach
                </select>
                @if ($categories->isEmpty())
                    <p class="mt-2 text-sm text-amber-300">Add a category to the election first (Elections → Edit → Category).</p>
                @endif
            </div>

            @include('admin.partials.form-input', ['label' => 'Display name', 'name' => 'display_name', 'value' => optional($candidate)->display_name, 'required' => true])

            <div>
                <label class="block text-sm font-medium text-slate-300">Campaign</label>
                <select id="candidate-partylist-id" name="partylist_id" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <option value="">— Independent (no campaign) —</option>
                    @foreach ($campaigns as $campaign)
                        <option
                            value="{{ $campaign['id'] }}"
                            data-election-id="{{ $campaign['election_id'] }}"
                            @selected(old('partylist_id', optional($candidate)->partylist_id) == $campaign['id'])
                        >{{ $campaign['name'] }}@if ($campaign['acronym']) ({{ $campaign['acronym'] }})@endif</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Only campaigns attached to the selected election are shown.</p>
                @error('partylist_id')<p class="mt-1 text-sm text-rose-400">{{ $message }}</p>@enderror
            </div>

            @include('admin.partials.form-input', ['label' => 'Grade level', 'name' => 'grade_level', 'value' => optional($candidate)->grade_level])
            @include('admin.partials.form-input', ['label' => 'Section', 'name' => 'section', 'value' => optional($candidate)->section])

            @php
                $existingPhotoUrl = ($isEdit && \App\Support\EventImageUrl::hasUploadedImage(optional($candidate)->photo_path))
                    ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                    : null;
            @endphp
            <div x-data="candidatePhoto(@js($existingPhotoUrl))">
                <label class="block text-sm font-medium text-slate-300">Profile Photo</label>

                <div class="mt-2 flex items-start gap-4">
                    {{-- Preview --}}
                    <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-full border border-slate-700 bg-slate-800">
                        <template x-if="preview">
                            <img :src="preview" alt="Photo preview" class="h-full w-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="flex h-full w-full items-center justify-center text-slate-500">
                                <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
                            </div>
                        </template>
                    </div>

                    {{-- Drop zone --}}
                    <div class="flex-1">
                        <div
                            @click="$refs.photoInput.click()"
                            @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="handleDrop($event)"
                            :class="dragging ? 'border-violet-400 bg-violet-500/10' : 'border-slate-700 bg-slate-950/50'"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed px-4 py-6 text-center transition"
                        >
                            <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 7.5L12 3m0 0L7.5 7.5M12 3v13.5" /></svg>
                            <p class="mt-2 text-sm text-slate-300">
                                <span class="font-semibold text-violet-300">Click to upload</span> or drag and drop
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Recommended: <span class="text-slate-300">600 × 600 px</span> · Square (1:1) · JPG, JPEG, PNG or WebP · Max 2MB</p>
                        </div>

                        <input x-ref="photoInput" type="file" name="photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               class="hidden" @change="handleFile($event)">

                        {{-- Hidden flag to request removal of an existing photo --}}
                        <input type="hidden" name="remove_photo" :value="removed ? 1 : 0">

                        <div class="mt-2 flex items-center gap-3" x-show="preview || removed" x-cloak>
                            <button type="button" @click="$refs.photoInput.click()" class="text-xs font-semibold text-violet-300 hover:text-violet-200">Replace</button>
                            <button type="button" x-show="preview" @click="removePhoto()" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Remove</button>
                            <span x-show="removed && !preview" x-cloak class="text-xs text-slate-500">Photo will be removed on save.</span>
                        </div>

                        <p x-show="error" x-cloak class="mt-2 text-xs text-rose-300" x-text="error"></p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Platform</label>
                <textarea name="platform" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('platform', optional($candidate)->platform) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Biography</label>
                <textarea name="biography" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('biography', optional($candidate)->biography) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Campaign promises</label>
                <textarea name="campaign_promises" rows="4" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('campaign_promises', optional($candidate)->campaign_promises) }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($candidate)->is_active ?? true)) class="rounded border-slate-700 bg-slate-950/50 text-cyan-500" />
                Active candidate
            </label>

            <div class="flex gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950">Save</button>
                <a href="{{ isset($candidate) && $candidate->election_id ? route('admin.elections.edit', $candidate->election_id) : route('admin.elections.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300">Cancel</a>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const electionSelect = document.getElementById('candidate-election-id');
                const categorySelect = document.getElementById('candidate-category-id');
                const partylistSelect = document.getElementById('candidate-partylist-id');

                if (!electionSelect || !categorySelect) {
                    return;
                }

                const buildFilter = (select, { keepFirst = false } = {}) => {
                    const options = [...select.options].map((option) => ({
                        value: option.value,
                        label: option.textContent,
                        electionId: option.dataset.electionId,
                    }));
                    const firstOption = keepFirst ? options[0] : null;

                    return () => {
                        const electionId = electionSelect.value;
                        const previous = select.value;
                        select.innerHTML = '';

                        if (firstOption) {
                            const el = document.createElement('option');
                            el.value = firstOption.value;
                            el.textContent = firstOption.label;
                            select.appendChild(el);
                        }

                        options
                            .filter((option) => option.electionId && option.electionId === electionId)
                            .forEach((option) => {
                                const el = document.createElement('option');
                                el.value = option.value;
                                el.textContent = option.label;
                                el.dataset.electionId = option.electionId;
                                if (option.value === previous) {
                                    el.selected = true;
                                }
                                select.appendChild(el);
                            });

                        if (!select.value && select.options.length > 0) {
                            select.selectedIndex = 0;
                        }
                    };
                };

                const syncCategories = buildFilter(categorySelect);
                const syncCampaigns = partylistSelect ? buildFilter(partylistSelect, { keepFirst: true }) : () => {};

                electionSelect.addEventListener('change', () => {
                    syncCategories();
                    syncCampaigns();
                });
                syncCategories();
                syncCampaigns();
            });

            function candidatePhoto(existingUrl) {
                return {
                    preview: existingUrl || null,
                    removed: false,
                    dragging: false,
                    error: '',
                    maxBytes: 2 * 1024 * 1024,
                    allowed: ['image/jpeg', 'image/png', 'image/webp'],

                    handleFile(event) {
                        const file = event.target.files?.[0];
                        this.validateAndPreview(file);
                    },

                    handleDrop(event) {
                        this.dragging = false;
                        const file = event.dataTransfer?.files?.[0];
                        if (!file) return;
                        this.$refs.photoInput.files = event.dataTransfer.files;
                        this.validateAndPreview(file);
                    },

                    validateAndPreview(file) {
                        this.error = '';
                        if (!file) return;

                        if (!this.allowed.includes(file.type)) {
                            this.error = 'Only JPG, JPEG, PNG, or WebP images are allowed.';
                            this.$refs.photoInput.value = '';
                            return;
                        }
                        if (file.size > this.maxBytes) {
                            this.error = 'Image must be 2 MB or smaller.';
                            this.$refs.photoInput.value = '';
                            return;
                        }

                        this.removed = false;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.preview = e.target.result; };
                        reader.readAsDataURL(file);
                    },

                    removePhoto() {
                        this.preview = null;
                        this.removed = true;
                        this.error = '';
                        this.$refs.photoInput.value = '';
                    },
                };
            }
        </script>
        <style>[x-cloak]{display:none !important;}</style>
    </x-admin-portal>
</x-app-layout>
