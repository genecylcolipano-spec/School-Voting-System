@php
    $service = app(\App\Services\Campaign\StudentCampaignService::class);
    $relevantElection = $service->relevantElection($campaign);
    $buttonState = $service->buttonStateFor($campaign, auth()->user());
    $accent = $campaign->color ?: '#22d3ee';
    $bannerUrl = $campaign->bannerUrl();
    $logoUrl = $campaign->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($campaign->logo_path) : null;
@endphp

<article class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70">
    <div class="relative aspect-video w-full overflow-hidden bg-slate-950">
        @if ($bannerUrl)
            <div class="absolute inset-0 flex items-center justify-center">
                @include('student.campaigns._banner-media', [
                    'url' => $bannerUrl,
                    'alt' => $campaign->name.' banner',
                    'contain' => $campaign->bannerNeedsContainLayout(),
                ])
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
        @else
            <div
                class="absolute inset-0"
                style="background: linear-gradient(135deg, {{ $accent }}18 0%, rgb(2 6 23) 45%, rgb(15 23 42) 100%)"
                aria-hidden="true"
            >
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, {{ $accent }}40 0%, transparent 45%), radial-gradient(circle at 80% 70%, rgb(56 189 248 / 0.15) 0%, transparent 40%);"></div>
            </div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
        @endif
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $campaign->name }} logo" loading="lazy" class="absolute bottom-2 left-3 z-[2] h-12 w-12 rounded-lg border border-slate-700 object-cover shadow-lg shadow-black/30">
        @endif
    </div>
    <div class="p-5">
        <div class="mb-2 flex items-center justify-between gap-2">
            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" style="background: {{ $accent }}20; color: {{ $accent }}">Campaign</span>
            @if ($campaign->acronym)
                <span class="text-xs font-semibold" style="color: {{ $accent }}">{{ $campaign->acronym }}</span>
            @endif
        </div>
        <h3 class="text-lg font-semibold text-white">{{ $campaign->name }}</h3>
        @if ($relevantElection)
            <p class="mt-1 text-xs text-slate-500">{{ $relevantElection->title }}</p>
        @endif
        @if ($campaign->motto)
            <p class="mt-2 text-sm italic text-slate-400">"{{ $campaign->motto }}"</p>
        @endif
        @if ($campaign->platform)
            <p class="mt-2 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($campaign->platform, 120) }}</p>
        @endif

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('student.campaigns.show', $campaign) }}" class="inline-block rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950">
                View campaign
            </a>
            @if ($buttonState['enabled'] && $buttonState['url'])
                <a href="{{ $buttonState['url'] }}" class="inline-block rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color: {{ $accent }}55; color: {{ $accent }}">
                    {{ $buttonState['label'] }}
                </a>
            @else
                <span class="inline-block rounded-xl border border-slate-700 bg-slate-800/60 px-4 py-2 text-xs font-semibold text-slate-400">
                    {{ $buttonState['label'] }}
                </span>
            @endif
        </div>
    </div>
</article>
