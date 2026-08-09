<x-app-layout>
    <x-faculty-portal title="Score Performance" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.judging.show', $competition) }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to {{ $competition->title }}</a>
            @if ($locked)
                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">Submitted</span>
            @elseif (! $acceptingScores)
                <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">Judging closed</span>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-xl font-bold text-white">{{ $entry->display_name }}</h2>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $entry->performance_title ?: 'Untitled performance' }}
                    @if ($entry->grade_level || $entry->section)
                        · {{ trim(($entry->grade_level ?? '').' '.($entry->section ?? '')) }}
                    @endif
                </p>

                @if ($entry->performance_description)
                    <p class="mt-4 whitespace-pre-line text-sm text-slate-300">{{ $entry->performance_description }}</p>
                @endif

                @if ($entry->video_path || $entry->video_url)
                    <div class="mt-5 overflow-hidden rounded-xl border border-slate-800 bg-black">
                        @if ($entry->video_path)
                            <video controls class="aspect-video w-full" src="{{ route('talent.video.stream', $entry) }}"></video>
                        @elseif ($entry->video_url)
                            <div class="p-4 text-sm">
                                <a href="{{ $entry->video_url }}" target="_blank" rel="noopener" class="font-semibold text-teal-300 hover:text-teal-200">Open performance video</a>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="mt-5 rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">No video uploaded for this performance.</p>
                @endif
            </section>

            <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h3 class="text-lg font-semibold text-white">Score sheet</h3>
                <p class="mt-1 text-sm text-slate-400">Enter points for each criterion. Max total: {{ $criteria->sum('max_points') }}.</p>

                <form method="POST" class="mt-5 space-y-4" x-data="{
                    scores: {
                        @foreach ($criteria as $criterion)
                            '{{ $criterion->id }}': {{ old('scores.'.$criterion->id, $existingScores[$criterion->id] ?? 0) }},
                        @endforeach
                    },
                    get total() {
                        return Object.values(this.scores).reduce((sum, value) => sum + (parseFloat(value) || 0), 0);
                    }
                }">
                    @csrf

                    @foreach ($criteria as $criterion)
                        <div>
                            <label class="flex items-center justify-between text-sm font-medium text-slate-200">
                                <span>{{ $criterion->name }}</span>
                                <span class="text-xs text-slate-500">max {{ $criterion->max_points }}</span>
                            </label>
                            <input
                                type="number"
                                name="scores[{{ $criterion->id }}]"
                                x-model.number="scores['{{ $criterion->id }}']"
                                min="0"
                                max="{{ $criterion->max_points }}"
                                step="0.5"
                                @disabled($locked || (! $acceptingScores && ! $sheet))
                                class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-500 focus:outline-none"
                                @required(! $locked)
                            >
                            @error('scores.'.$criterion->id)
                                <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div>
                        <label class="text-sm font-medium text-slate-200">Notes (optional)</label>
                        <textarea
                            name="notes"
                            rows="3"
                            @disabled($locked)
                            class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white focus:border-teal-500 focus:outline-none"
                        >{{ old('notes', $sheet?->notes) }}</textarea>
                    </div>

                    <div class="rounded-xl border border-teal-500/20 bg-teal-500/10 px-4 py-3 text-sm text-teal-100">
                        Running total: <span class="font-bold" x-text="total.toFixed(2)">{{ number_format((float) ($sheet?->total_score ?? 0), 2) }}</span>
                    </div>

                    @unless ($locked)
                        <div class="flex flex-wrap gap-3 pt-2">
                            <button
                                type="submit"
                                formaction="{{ route('faculty.judging.draft', [$competition, $entry]) }}"
                                @disabled(! $acceptingScores && ! $sheet)
                                class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800 disabled:opacity-40"
                            >
                                Save draft
                            </button>
                            <button
                                type="submit"
                                formaction="{{ route('faculty.judging.submit', [$competition, $entry]) }}"
                                @disabled(! $acceptingScores)
                                class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950 disabled:opacity-40"
                                onclick="return confirm('Submit these scores? You will not be able to edit them afterward.');"
                            >
                                Submit scores
                            </button>
                        </div>
                    @endunless
                </form>
            </section>
        </div>
    </x-faculty-portal>
</x-app-layout>
