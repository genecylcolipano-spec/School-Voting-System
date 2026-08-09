@php
    $posterItems = $campaign->approvedPosters
        ->filter(fn ($poster) => $poster->hasUploadedFile())
        ->map(fn ($poster) => [
            'url' => $poster->file_url,
            'title' => $poster->title ?: 'Campaign poster',
        ])
        ->values();
@endphp

<section
    class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6"
    @if ($posterItems->isNotEmpty())
        x-data="{
            open: false,
            current: 0,
            posters: @js($posterItems),
            openAt(index) {
                this.current = index;
                this.open = true;
            },
            close() {
                this.open = false;
            },
            prev() {
                if (! this.open || this.posters.length < 2) return;
                this.current = (this.current - 1 + this.posters.length) % this.posters.length;
            },
            next() {
                if (! this.open || this.posters.length < 2) return;
                this.current = (this.current + 1) % this.posters.length;
            },
        }"
        @keydown.escape.window="close()"
        @keydown.arrow-left.window="prev()"
        @keydown.arrow-right.window="next()"
    @endif
>
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Official Posters</h2>

    @if ($posterItems->isNotEmpty())
        <div @class([
            'mt-4 grid gap-4',
            'grid-cols-1' => $posterItems->count() === 1,
            'grid-cols-1 sm:grid-cols-2' => $posterItems->count() > 1,
        ])>
            @foreach ($posterItems as $index => $poster)
                <button
                    type="button"
                    @click="openAt({{ $index }})"
                    class="group w-full overflow-hidden rounded-xl border border-slate-800 bg-slate-950/50 p-2 text-left shadow-lg shadow-black/20 transition hover:border-cyan-500/30 hover:shadow-cyan-500/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/50"
                >
                    <img
                        src="{{ $poster['url'] }}"
                        alt="{{ $poster['title'] }}"
                        loading="lazy"
                        class="mx-auto w-full max-h-[28rem] rounded-lg object-contain transition group-hover:opacity-95"
                    >
                </button>
            @endforeach
        </div>

        <div
            x-show="open"
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
            :aria-label="posters[current]?.title ?? 'Campaign poster preview'"
        >
            <div class="absolute inset-0 bg-slate-950/95 backdrop-blur-sm" @click="close()"></div>

            <div class="relative z-10 flex w-full max-w-6xl flex-col items-center" @click.stop>
                <button
                    type="button"
                    @click="close()"
                    class="absolute -top-2 right-0 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-700 bg-slate-900/90 text-slate-300 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white sm:-top-4 sm:right-0"
                    aria-label="Close poster preview"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <template x-if="posters.length > 1">
                    <button
                        type="button"
                        @click="prev()"
                        class="absolute left-0 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-700 bg-slate-900/90 text-slate-300 transition hover:border-cyan-500/40 hover:bg-slate-800 hover:text-white sm:-left-14"
                        aria-label="Previous poster"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </template>

                <img
                    :src="posters[current]?.url"
                    :alt="posters[current]?.title ?? 'Campaign poster'"
                    class="max-h-[85vh] w-full rounded-xl border border-slate-800 object-contain shadow-2xl shadow-black/40"
                >

                <p x-show="posters[current]?.title" x-text="posters[current]?.title" class="mt-4 max-w-2xl text-center text-sm text-slate-400"></p>

                <template x-if="posters.length > 1">
                    <button
                        type="button"
                        @click="next()"
                        class="absolute right-0 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-slate-700 bg-slate-900/90 text-slate-300 transition hover:border-cyan-500/40 hover:bg-slate-800 hover:text-white sm:-right-14"
                        aria-label="Next poster"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </template>

                <p x-show="posters.length > 1" class="mt-3 text-xs text-slate-500">
                    <span x-text="current + 1"></span> / <span x-text="posters.length"></span>
                </p>
            </div>
        </div>
    @else
        <div class="mt-4 flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-700/80 bg-slate-950/40 px-6 py-12 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900/80 text-slate-500">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <p class="mt-4 text-sm text-slate-400">No campaign posters available.</p>
        </div>
    @endif
</section>
