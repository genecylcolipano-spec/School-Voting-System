<x-app-layout>
    @php
        $badgePalette = [
            'bg-violet-500/15 text-violet-200',
            'bg-emerald-500/15 text-emerald-200',
            'bg-amber-500/15 text-amber-200',
            'bg-rose-500/15 text-rose-200',
            'bg-sky-500/15 text-sky-200',
        ];
    @endphp

    <x-faculty-portal title="{{ $election->title }}" :user="$user" :notifications-count="$notificationsCount">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('faculty.elections.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Back to elections</a>
            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-200">View only</span>
        </div>

        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="text-xl font-bold text-white sm:text-2xl">{{ $election->title }}</h2>
                    @if ($election->description)
                        <p class="mt-2 text-sm text-slate-300">{{ $election->description }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $election->status?->value ?? $election->status }}</p>
                    @if ($election->voting_starts_at)
                        <p class="mt-1 text-xs text-slate-400">Starts {{ $election->voting_starts_at->format('M d, Y g:i A') }}</p>
                    @endif
                    @if ($election->voting_ends_at)
                        <p class="text-xs text-slate-400">Ends {{ $election->voting_ends_at->format('M d, Y g:i A') }}</p>
                    @endif
                </div>
            </div>
        </section>

        <div class="space-y-6">
            @forelse ($election->categories as $category)
                @php
                    $candidates = $election->activeCandidates->where('election_category_id', $category->id);
                @endphp
                <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h3 class="text-lg font-semibold text-white">{{ $category->name }}</h3>
                    @if ($category->description)
                        <p class="mt-1 text-sm text-slate-400">{{ $category->description }}</p>
                    @endif

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($candidates as $candidate)
                            @php
                                $party = $candidate->party_or_group ?: 'Independent';
                                $badge = $party === 'Independent'
                                    ? 'bg-slate-700/60 text-slate-300'
                                    : $badgePalette[crc32($party) % count($badgePalette)];
                                $photo = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                    ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                    : null;
                            @endphp
                            <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                                <div class="flex items-start gap-3">
                                    @if ($photo)
                                        <img src="{{ $photo }}" alt="" class="h-12 w-12 rounded-full object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-500/15 text-sm font-semibold text-teal-200">
                                            {{ strtoupper(substr($candidate->display_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-white">{{ $candidate->display_name }}</p>
                                        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">{{ $party }}</span>
                                        @if ($candidate->platform)
                                            <p class="mt-2 line-clamp-3 text-xs text-slate-400">{{ $candidate->platform }}</p>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500 sm:col-span-2 lg:col-span-3">No active candidates for this position.</p>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-700 px-4 py-8 text-center text-sm text-slate-500">
                    No positions have been configured for this election yet.
                </div>
            @endforelse
        </div>
    </x-faculty-portal>
</x-app-layout>
