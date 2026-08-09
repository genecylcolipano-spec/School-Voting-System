<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <a href="{{ url()->previous() }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Back</a>

            <article class="mt-6 overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                <div class="grid gap-6 p-6 md:grid-cols-[160px_1fr]">
                    <x-candidate-avatar :path="$candidate->photo_path" :name="$candidate->display_name" size="xl" class="mx-auto !h-40 !w-40" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-cyan-300">{{ $candidate->category?->name ?? $candidate->position ?? 'Candidate' }}</p>
                        <h1 class="mt-1 text-3xl font-bold text-white">{{ $candidate->display_name }}</h1>
                        <p class="mt-2 text-sm text-slate-400">{{ $candidate->party_or_group ?: 'Independent' }}</p>
                        @if ($grade || $section)
                            <p class="mt-1 text-sm text-slate-500">Grade {{ $grade ?? '—' }} · Section {{ $section ?? '—' }}</p>
                        @endif
                        <p class="mt-2 text-xs text-slate-500">{{ $candidate->election?->title }}</p>
                    </div>
                </div>

                <div class="space-y-6 border-t border-slate-800 p-6">
                    @if ($candidate->platform)
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Platform</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $candidate->platform }}</p>
                        </section>
                    @endif

                    @if ($candidate->biography)
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Biography</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $candidate->biography }}</p>
                        </section>
                    @endif

                    @if ($candidate->campaign_promises)
                        <section>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Campaign Promises</h2>
                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $candidate->campaign_promises }}</p>
                        </section>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
