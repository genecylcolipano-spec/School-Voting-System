@props([
    'spotlight' => [],
    'primary' => null,
    'publishedAt' => null,
    'publishedTime' => null,
    'publishedBy' => null,
    'theme' => 'admin',
])

@php
    $isAdmin = $theme === 'admin';
    $cardBorder = $isAdmin ? 'border-violet-500/20' : 'border-cyan-500/20';
    $accent = $isAdmin ? 'text-violet-300' : 'text-cyan-300';
    $entries = collect($spotlight);
@endphp

@if ($entries->isNotEmpty())
    <section class="vm-spotlight mb-6 rounded-2xl border {{ $cardBorder }} bg-gradient-to-br from-slate-900/90 to-slate-950/80 p-5 sm:p-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-2xl" aria-hidden="true">🏆</p>
                <h2 class="mt-1 text-xl font-bold text-white">Winner Spotlight</h2>
                <p class="mt-1 text-sm text-slate-400">Official winning candidates by position.</p>
            </div>
            @if ($publishedAt || $publishedBy)
                <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3 text-sm">
                    @if ($publishedBy)
                        <p class="text-slate-400">Published by <span class="font-semibold text-white">{{ $publishedBy }}</span></p>
                    @endif
                    @if ($publishedAt)
                        <p class="mt-1 text-slate-500">{{ $publishedAt }}@if($publishedTime) · {{ $publishedTime }}@endif</p>
                    @endif
                </div>
            @endif
        </div>

        @if ($primary)
            <article class="mb-5 overflow-hidden rounded-2xl border {{ $cardBorder }} bg-slate-950/50">
                <div class="grid gap-5 p-5 md:grid-cols-[140px_1fr] md:items-center">
                    <div class="mx-auto h-32 w-32 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                        @if (! empty($primary['photo_url']))
                            <img src="{{ $primary['photo_url'] }}" alt="{{ $primary['name'] }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-4xl text-slate-600">👤</div>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $accent }}">{{ $primary['label'] ?? $primary['position'] ?? 'Winner' }}</p>
                        <h3 class="mt-1 text-2xl font-bold text-white">{{ $primary['name'] }}</h3>
                        @if (! empty($primary['party']))
                            <p class="mt-1 text-sm text-slate-400">{{ $primary['party'] }}</p>
                        @endif
                        <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-900/70 px-3 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Final Votes</dt>
                                <dd class="mt-1 text-lg font-bold text-white">{{ number_format($primary['votes'] ?? 0) }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-900/70 px-3 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Vote %</dt>
                                <dd class="mt-1 text-lg font-bold text-emerald-300">{{ number_format($primary['percent'] ?? 0, 1) }}%</dd>
                            </div>
                            <div class="rounded-xl bg-slate-900/70 px-3 py-2">
                                <dt class="text-[10px] uppercase tracking-wide text-slate-500">Margin</dt>
                                <dd class="mt-1 text-lg font-bold text-amber-300">{{ number_format($primary['margin_votes'] ?? 0) }} votes</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </article>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($entries->reject(fn ($w) => $primary && ($w['name'] ?? '') === ($primary['name'] ?? '') && ($w['label'] ?? '') === ($primary['label'] ?? '')) as $winner)
                <article class="rounded-xl border border-slate-800 bg-slate-950/40 p-4 transition hover:border-violet-500/25">
                    <div class="flex items-start gap-3">
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-800 bg-slate-900">
                            @if (! empty($winner['photo_url']))
                                <img src="{{ $winner['photo_url'] }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xl text-slate-600">👤</div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-semibold uppercase tracking-wide {{ $accent }}">{{ $winner['label'] ?? $winner['position'] }}</p>
                            <p class="truncate font-semibold text-white">{{ $winner['name'] }}</p>
                            @if (! empty($winner['party']))
                                <p class="truncate text-xs text-slate-400">{{ $winner['party'] }}</p>
                            @endif
                            <p class="mt-2 text-xs text-slate-300">{{ number_format($winner['votes'] ?? 0) }} votes · {{ number_format($winner['percent'] ?? 0, 1) }}% · +{{ number_format($winner['margin_votes'] ?? 0) }} margin</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
