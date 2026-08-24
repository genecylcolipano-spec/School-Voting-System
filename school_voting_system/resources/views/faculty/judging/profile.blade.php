<x-app-layout>
    <x-faculty-portal title="{{ $entry->display_name }}" :user="$user" :notifications-count="$notificationsCount">
        @php
            $routeParams = filled($from)
                ? [$competition, $entry, 'from' => $from]
                : [$competition, $entry];
            $cta = $sheet?->isLocked() ? 'View scores' : ($sheet ? 'Continue' : 'Score');
            $category = $entry->talentCategoryLabel();
        @endphp

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ $backUrl }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; {{ $backLabel }}</a>
            <a
                href="{{ route('faculty.judging.score', $routeParams) }}"
                class="rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 px-4 py-2 text-sm font-semibold text-slate-950"
            >
                {{ $cta }}
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70 lg:col-span-1">
                <div class="aspect-square bg-slate-950">
                    @include('faculty.judging._entry-photo', ['entry' => $entry, 'initialClass' => 'text-5xl'])
                </div>
                <div class="p-5">
                    <h1 class="text-xl font-bold text-white">{{ $entry->display_name }}</h1>
                    @if ($category)
                        <p class="mt-2">
                            <span class="rounded-full border border-teal-400/30 bg-teal-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-200">{{ $category }}</span>
                        </p>
                    @endif
                    <p class="mt-3 text-sm font-semibold text-teal-200">{{ $entry->performance_title ?: 'Untitled performance' }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $competition->title }}</p>
                </div>
            </section>

            <div class="space-y-6 lg:col-span-2">
                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-teal-300">Participant</h2>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Grade / Year</dt>
                            <dd class="mt-1 font-medium text-white">{{ $entry->grade_level ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-500">Section</dt>
                            <dd class="mt-1 font-medium text-white">{{ $entry->section ?: '—' }}</dd>
                        </div>
                        @if ($entry->course_strand)
                            <div class="sm:col-span-2">
                                <dt class="text-xs uppercase tracking-wide text-slate-500">Course / Strand</dt>
                                <dd class="mt-1 font-medium text-white">{{ $entry->course_strand }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>

                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-teal-300">About this performance</h2>
                    @if ($entry->profile_summary)
                        <p class="mt-4 text-sm leading-relaxed text-slate-300">{{ $entry->profile_summary }}</p>
                    @endif
                    @if ($entry->performance_description)
                        <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $entry->performance_description }}</p>
                    @endif
                    @if ($entry->social_media)
                        <p class="mt-4 text-sm text-teal-200">{{ $entry->social_media }}</p>
                    @endif
                    @if (! $entry->profile_summary && ! $entry->performance_description && ! $entry->social_media)
                        <p class="mt-4 text-sm text-slate-500">No profile details were provided for this performance.</p>
                    @endif
                </section>
            </div>
        </div>
    </x-faculty-portal>
</x-app-layout>
