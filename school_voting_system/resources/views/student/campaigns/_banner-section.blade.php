@php
    $accent = $accent ?? ($campaign->color ?: '#22d3ee');
    $bannerUrl = $campaign->bannerUrl();
    $logoUrl = $campaign->logo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($campaign->logo_path)
        : null;
    $hasBanner = filled($bannerUrl);
@endphp

<section
    class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-950 shadow-lg shadow-black/20"
    @if ($hasBanner)
        x-data="{ previewOpen: false }"
        @keydown.escape.window="previewOpen = false"
    @endif
>
    <div class="relative aspect-video w-full overflow-hidden">
        @if ($hasBanner)
            <button
                type="button"
                @click="previewOpen = true"
                class="group absolute inset-0 z-0 flex h-full w-full cursor-zoom-in items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cyan-400/60"
                aria-label="View full campaign banner"
            >
                @include('student.campaigns._banner-media', [
                    'url' => $bannerUrl,
                    'alt' => $campaign->name.' banner',
                    'contain' => $campaign->bannerNeedsContainLayout(),
                    'class' => 'transition duration-300 group-hover:opacity-95',
                ])
            </button>
        @else
            <div
                class="absolute inset-0"
                style="background: linear-gradient(135deg, {{ $accent }}18 0%, rgb(2 6 23) 45%, rgb(15 23 42) 100%)"
                aria-hidden="true"
            >
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, {{ $accent }}40 0%, transparent 45%), radial-gradient(circle at 80% 70%, rgb(56 189 248 / 0.15) 0%, transparent 40%);"></div>
                <div class="absolute inset-0 bg-[linear-gradient(to_right,rgb(148_163_184_/_0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgb(148_163_184_/_0.06)_1px,transparent_1px)] bg-[size:2rem_2rem]"></div>
            </div>
        @endif

        <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/25"></div>
        <div class="pointer-events-none absolute inset-0 z-[1] bg-gradient-to-r from-slate-950/80 via-transparent to-transparent"></div>

        <div class="relative z-[2] flex h-full min-h-[inherit] flex-col justify-end p-5 sm:p-6 lg:p-8">
            <div class="flex flex-wrap items-end gap-4 sm:gap-5">
                @if ($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $campaign->name }} logo"
                        loading="lazy"
                        class="h-14 w-14 shrink-0 rounded-xl border border-slate-700/80 bg-slate-950/40 object-cover shadow-lg shadow-black/30 sm:h-16 sm:w-16 lg:h-20 lg:w-20"
                    >
                @else
                    <span
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-slate-700/80 text-lg font-bold text-white shadow-lg shadow-black/30 sm:h-16 sm:w-16 sm:text-xl lg:h-20 lg:w-20 lg:text-2xl"
                        style="background: {{ $accent }}25; color: {{ $accent }}"
                    >
                        {{ strtoupper(substr($campaign->acronym ?: $campaign->name, 0, 2)) }}
                    </span>
                @endif

                <div class="min-w-0 flex-1">
                    @if ($campaign->acronym)
                        <p class="text-xs font-semibold uppercase tracking-wider sm:text-sm" style="color: {{ $accent }}">
                            {{ $campaign->acronym }}
                        </p>
                    @endif
                    <h1 class="mt-0.5 text-xl font-bold leading-tight text-white drop-shadow-sm sm:text-2xl lg:text-3xl">
                        {{ $campaign->name }}
                    </h1>
                    @if ($campaign->motto)
                        <p class="mt-2 max-w-2xl text-sm italic leading-relaxed text-slate-200/90 sm:text-base">
                            "{{ $campaign->motto }}"
                        </p>
                    @endif
                    @if ($campaign->leader)
                        <p class="mt-2 text-xs text-slate-400 sm:text-sm">Led by {{ $campaign->leader }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($hasBanner)
        <div
            x-show="previewOpen"
            x-cloak
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8"
            role="dialog"
            aria-modal="true"
            aria-label="Campaign banner preview"
        >
            <div class="absolute inset-0 bg-slate-950/95 backdrop-blur-sm" @click="previewOpen = false"></div>

            <div class="relative z-10 w-full max-w-6xl" @click.stop>
                <button
                    type="button"
                    @click="previewOpen = false"
                    class="absolute -top-2 right-0 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900/90 text-slate-300 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white sm:-top-4"
                    aria-label="Close banner preview"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <img
                    src="{{ $bannerUrl }}"
                    alt="{{ $campaign->name }} banner"
                    class="max-h-[85vh] w-full rounded-xl border border-slate-800 object-contain shadow-2xl shadow-black/40"
                >
            </div>
        </div>
    @endif
</section>
