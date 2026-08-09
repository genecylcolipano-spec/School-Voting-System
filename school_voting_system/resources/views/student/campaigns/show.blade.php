@php
    $accent = $campaign->color ?: '#22d3ee';
@endphp

<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-end">
                <a href="{{ route('student.campaigns.index') }}" class="shrink-0 rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    All campaigns
                </a>
            </div>

            @include('student.campaigns._banner-section', ['campaign' => $campaign, 'accent' => $accent])

            <div class="mt-6 space-y-6">
                @if ($campaign->description || $campaign->platform)
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Campaign Information</h2>

                        @if ($campaign->description)
                            <div class="mt-4">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">About</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $campaign->description }}</p>
                            </div>
                        @endif

                        @if ($campaign->platform)
                            <div class="{{ $campaign->description ? 'mt-5 border-t border-slate-800 pt-5' : 'mt-4' }}">
                                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Platform</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-300">{{ $campaign->platform }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                {{-- Candidates for the relevant election only --}}
                @if ($relevantElection && $campaignCandidates->isNotEmpty())
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Candidates</h2>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400">{{ $relevantElection->title }}</span>
                                <x-admin-status-badge :status="$relevantElection->status?->value ?? 'draft'" />
                            </div>
                        </div>
                        @if ($relevantElection->voting_starts_at)
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $relevantElection->voting_starts_at->format('M j, Y g:i A') }}
                                @if ($relevantElection->voting_ends_at) – {{ $relevantElection->voting_ends_at->format('M j, Y g:i A') }} @endif
                            </p>
                        @endif

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($campaignCandidates as $candidate)
                                @php
                                    $candPhoto = \App\Support\EventImageUrl::hasUploadedImage($candidate->photo_path)
                                        ? \App\Support\EventImageUrl::resolve($candidate->photo_path)
                                        : null;
                                @endphp
                                <div class="flex items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                                    @if ($candPhoto)
                                        <img src="{{ $candPhoto }}" alt="{{ $candidate->display_name }}" class="h-14 w-14 rounded-full border border-slate-700 object-cover">
                                    @else
                                        <span class="flex h-14 w-14 items-center justify-center rounded-full border border-slate-700 bg-slate-800 text-slate-500">
                                            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z" /></svg>
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-white">{{ $candidate->display_name }}</p>
                                        <p class="truncate text-xs" style="color: {{ $accent }}">{{ $candidate->category?->name ?? $candidate->position }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @elseif ($relevantElection)
                    <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Candidates</h2>
                        <p class="mt-3 text-sm text-slate-400">No active candidates listed for this campaign in {{ $relevantElection->title }}.</p>
                    </section>
                @endif

                @include('student.campaigns._posters-section', ['campaign' => $campaign])

                @include('student.campaigns._action-bar', [
                    'buttonState' => $buttonState,
                    'accent' => $accent,
                    'election' => $relevantElection,
                ])
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none !important;}</style>
</x-app-layout>
