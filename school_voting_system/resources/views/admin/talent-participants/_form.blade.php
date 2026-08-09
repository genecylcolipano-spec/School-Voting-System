@php
    $isEdit = isset($entry) && $entry !== null;
    $selectedEventId = old('talent_event_id', $isEdit ? $entry->talent_event_id : ($preselectedEvent ?? ''));
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('admin.talent-participants.update', $entry) : route('admin.talent-participants.store') }}"
    enctype="multipart/form-data"
    class="space-y-6"
>
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">{{ $isEdit ? 'Edit Participant' : 'Add Participant' }}</h2>
        <p class="mt-1 text-sm text-slate-400">Contestant profile and performance submission.</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @unless ($isEdit)
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-300">Competition</label>
                    <select name="talent_event_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        <option value="">Select competition…</option>
                        @foreach ($events as $event)
                            <option value="{{ $event->id }}" @selected((string) $selectedEventId === (string) $event->id)>{{ $event->title }}</option>
                        @endforeach
                    </select>
                    @error('talent_event_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    @if (($events ?? collect())->isEmpty())
                        <p class="mt-1 text-xs text-amber-300">No competitions allow admin-managed participants. Update Competition Settings → Registration Method.</p>
                    @endif
                </div>
            @else
                <div class="sm:col-span-2 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm text-slate-300">
                    Competition: <span class="font-semibold text-white">{{ $entry->talentEvent?->title }}</span>
                </div>
            @endunless

            <div>
                <label class="block text-sm font-medium text-slate-300">Full Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $entry->display_name ?? '') }}" required
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                @error('display_name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Student ID</label>
                <input type="text" name="student_id_number" value="{{ old('student_id_number', $entry->student_id_number ?? '') }}" placeholder="2026-00123"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                @error('student_id_number')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Grade / Year</label>
                <input type="text" name="grade_level" value="{{ old('grade_level', $entry->grade_level ?? '') }}" required placeholder="10 / 11 / 12"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                @error('grade_level')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Section</label>
                <input type="text" name="section" value="{{ old('section', $entry->section ?? '') }}" required placeholder="A"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                @error('section')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Course / Strand</label>
                <input type="text" name="course_strand" value="{{ old('course_strand', $entry->course_strand ?? '') }}" placeholder="STEM / ABM / HUMSS"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Talent Category</label>
                <select name="talent_category" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <option value="">— Use competition category —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(old('talent_category', $entry->talent_category?->value ?? '') === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Performance Title</label>
                <input type="text" name="performance_title" value="{{ old('performance_title', $entry->performance_title ?? '') }}"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Short Profile</label>
                <input type="text" name="profile_summary" value="{{ old('profile_summary', $entry->profile_summary ?? '') }}"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Performance Description</label>
                <textarea name="performance_description" rows="3" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ old('performance_description', $entry->performance_description ?? '') }}</textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-300">Social Media <span class="text-slate-500">(optional)</span></label>
                <input type="text" name="social_media" value="{{ old('social_media', $entry->social_media ?? '') }}"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Media</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-300">Profile Photo</label>
                @if ($isEdit && $entry->photoUrl())
                    <div class="mt-2 h-24 w-24 overflow-hidden rounded-full border border-slate-700">
                        <img src="{{ $entry->photoUrl() }}" alt="" class="h-full w-full object-cover object-center">
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1 file:text-violet-200">
                <p class="mt-1 text-xs text-slate-500">Recommended: <span class="text-slate-300">600 × 600 px</span> · Square (1:1) · Max 2MB</p>
                @error('photo')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Video Thumbnail</label>
                @if ($isEdit && $entry->thumbnail_path)
                    <img src="{{ $entry->thumbnailUrl() }}" alt="" class="mt-2 h-20 w-32 rounded-xl object-cover">
                @endif
                <input type="file" name="thumbnail" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-3 file:py-1 file:text-violet-200">
                @error('thumbnail')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Upload Performance Video</label>
                @if ($isEdit && $entry->video_path)
                    <p class="mt-1 text-xs text-emerald-300">Uploaded video on file.
                        <a href="{{ $entry->videoFileUrl() }}" target="_blank" class="underline">Watch</a>
                    </p>
                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-rose-300">
                        <input type="checkbox" name="remove_video" value="1" class="rounded border-slate-600 bg-slate-900 text-rose-500">
                        Remove uploaded video
                    </label>
                @endif
                <input type="file" name="video" accept="video/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-100 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-500/20 file:px-3 file:py-1 file:text-cyan-200">
                @error('video')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Video URL <span class="text-slate-500">(YouTube / Vimeo)</span></label>
                <input type="url" name="video_url" value="{{ old('video_url', $entry->video_url ?? '') }}" placeholder="https://youtu.be/…"
                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                @if ($isEdit && $entry->video_url)
                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-rose-300">
                        <input type="checkbox" name="clear_video_url" value="1" class="rounded border-slate-600 bg-slate-900 text-rose-500">
                        Clear video URL
                    </label>
                @endif
                @error('video_url')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    @unless ($isEdit)
        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 px-5 py-4">
            <label class="inline-flex items-center gap-3 text-sm text-slate-300">
                <input type="checkbox" name="approve_immediately" value="1" @checked(old('approve_immediately', true))
                    class="rounded border-slate-600 bg-slate-900 text-violet-500 focus:ring-violet-500/40">
                Approve immediately (skip pending review)
            </label>
            <p class="mt-1 text-xs text-slate-500">Uncheck to leave the entry as Pending for later review.</p>
        </section>
    @endunless

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
            {{ $isEdit ? 'Save Changes' : 'Add Participant' }}
        </button>
        <a href="{{ $isEdit ? route('admin.talent-participants.show', $entry) : route('admin.talent-participants.index') }}"
           class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm text-slate-300 hover:bg-slate-800">Cancel</a>
    </div>
</form>
