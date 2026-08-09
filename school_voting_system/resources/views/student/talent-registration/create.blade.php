<x-app-layout>
    @php
        $fields = $draft['fields'] ?? [];
        $old = fn (string $key, $default = '') => old($key, $fields[$key] ?? $default);
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6" x-data="{ mode: '{{ old('video_url', $fields['video_url'] ?? '') ? 'url' : 'upload' }}' }">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300">Step 1 of 2 · Registration Form</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Register for {{ $talentEvent->title }}</h1>
                <p class="mt-1 text-sm text-cyan-300">{{ $talentEvent->talent_category?->label() }}</p>
            </div>
            <a href="{{ route('student.talent-registration.show', $talentEvent) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                Cancel
            </a>
        </div>

        <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-xs text-slate-400">
            <p><span class="font-semibold text-slate-300">Video guidelines:</span>
                max duration {{ $talentEvent->maxVideoDurationLabel() }},
                max size {{ $talentEvent->maxUploadSizeMb() }} MB,
                accepted formats: {{ implode(', ', $talentEvent->acceptedVideoFormatsArray()) }}.</p>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('student.talent-registration.review.store', $talentEvent) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Full Name</label>
                    <input type="text" name="display_name" value="{{ $old('display_name', auth()->user()->name ?? '') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Student ID</label>
                    <input type="text" name="student_id_number" value="{{ $old('student_id_number') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Grade</label>
                    <input type="text" name="grade_level" value="{{ $old('grade_level') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Section</label>
                    <input type="text" name="section" value="{{ $old('section') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Course / Strand</label>
                    <input type="text" name="course_strand" value="{{ $old('course_strand') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Talent Category</label>
                    <select name="talent_category" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                        @foreach (\App\Enums\TalentCategory::cases() as $category)
                            <option value="{{ $category->value }}" @selected($old('talent_category', $talentEvent->talent_category?->value) === $category->value)>{{ $category->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Performance Title</label>
                <input type="text" name="performance_title" value="{{ $old('performance_title') }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Short Bio (optional)</label>
                <input type="text" name="profile_summary" value="{{ $old('profile_summary') }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300">Performance Description</label>
                <textarea name="performance_description" rows="3" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">{{ $old('performance_description') }}</textarea>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Profile Photo (optional)</label>
                    <input type="file" name="photo" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                    <p class="mt-1 text-xs text-slate-500">Recommended: 600 × 600 px · Square (1:1) · Max 2MB</p>
                    @if (! empty($draft['files']['photo']['name']))
                        <p class="mt-1 text-xs text-cyan-300">Previously selected: {{ $draft['files']['photo']['name'] }} — re-upload to replace.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Social Media (optional)</label>
                    <input type="text" name="social_media" value="{{ $old('social_media') }}" placeholder="@handle or link" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                </div>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                <div class="flex gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="video_mode" value="upload" x-model="mode" class="text-cyan-500 focus:ring-cyan-500/40"> Upload Video
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="video_mode" value="url" x-model="mode" class="text-cyan-500 focus:ring-cyan-500/40"> Video URL
                    </label>
                </div>

                <div class="mt-3" x-show="mode === 'upload'">
                    <label class="block text-sm font-medium text-slate-300">Performance Video</label>
                    <input type="file" name="video" accept="video/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                    <p class="mt-1 text-xs text-slate-500">Accepted: {{ implode(', ', $talentEvent->acceptedVideoFormatsArray()) }} · Max {{ $talentEvent->maxUploadSizeMb() }} MB.</p>
                    @if (! empty($draft['files']['video']['name']))
                        <p class="mt-1 text-xs text-cyan-300">Previously selected: {{ $draft['files']['video']['name'] }} — re-upload to replace.</p>
                    @endif
                </div>

                <div class="mt-3" x-show="mode === 'url'" x-cloak>
                    <label class="block text-sm font-medium text-slate-300">Video URL</label>
                    <input type="url" name="video_url" value="{{ $old('video_url') }}" placeholder="https://youtu.be/…" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100">
                    <p class="mt-1 text-xs text-slate-500">Paste a YouTube or Vimeo link to your performance.</p>
                </div>

                <div class="mt-3">
                    <label class="block text-sm font-medium text-slate-300">Video Thumbnail (optional)</label>
                    <input type="file" name="thumbnail" accept="image/*" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-sm text-slate-300 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-800 file:px-3 file:py-1.5 file:text-slate-200">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('student.talent-registration.show', $talentEvent) }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">Back</a>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-6 py-2.5 text-sm font-semibold text-slate-950 transition hover:from-cyan-400 hover:to-sky-300">Continue to Review</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <style>[x-cloak]{display:none !important;}</style>
    @endpush
</x-app-layout>
