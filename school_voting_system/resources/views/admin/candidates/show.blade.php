<x-app-layout>
    <x-admin-portal :title="$candidate->display_name" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.elections.edit', $candidate->election) }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to election</a>
                <h1 class="mt-2 text-2xl font-bold text-white">{{ $candidate->display_name }}</h1>
                <p class="mt-1 text-sm text-slate-400">{{ $candidate->category?->name }} · {{ $candidate->party_or_group ?: 'Independent' }}</p>
            </div>
            <a href="{{ route('admin.candidates.edit', $candidate) }}" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">Edit candidate</a>
        </div>

        <article class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-6">
            <div class="grid gap-6 md:grid-cols-[180px_1fr]">
                <x-candidate-avatar :path="$candidate->photo_path" :name="$candidate->display_name" size="xl" class="mx-auto !h-40 !w-40" />
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs uppercase text-slate-500">Position</dt><dd class="mt-1 font-medium text-white">{{ $candidate->category?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Partylist</dt><dd class="mt-1 font-medium text-white">{{ $candidate->party_or_group ?: 'Independent' }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Grade</dt><dd class="mt-1 font-medium text-white">{{ $candidate->grade_level ?: ($candidate->user?->grade_level ?? '—') }}</dd></div>
                    <div><dt class="text-xs uppercase text-slate-500">Section</dt><dd class="mt-1 font-medium text-white">{{ $candidate->section ?: ($candidate->user?->section ?? '—') }}</dd></div>
                </dl>
            </div>

            @if ($candidate->platform)
                <section class="mt-6 border-t border-slate-800 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Platform</h2>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-300">{{ $candidate->platform }}</p>
                </section>
            @endif
            @if ($candidate->biography)
                <section class="mt-6 border-t border-slate-800 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Biography</h2>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-300">{{ $candidate->biography }}</p>
                </section>
            @endif
            @if ($candidate->campaign_promises)
                <section class="mt-6 border-t border-slate-800 pt-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-violet-300">Campaign Promises</h2>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-300">{{ $candidate->campaign_promises }}</p>
                </section>
            @endif
        </article>
    </x-admin-portal>
</x-app-layout>
