<section id="posters" class="scroll-mt-28 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <h3 class="text-lg font-semibold text-white">Partylist & Posters</h3>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        @forelse ($partylists as $partylist)
            <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4" data-partylist-card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-white">{{ $partylist->name }}</h4>
                        @if ($partylist->acronym)
                            <p class="text-xs text-violet-300">{{ $partylist->acronym }}</p>
                        @endif
                        @if ($partylist->motto)
                            <p class="mt-1 text-xs italic text-slate-500">"{{ $partylist->motto }}"</p>
                        @endif
                    </div>
                    <x-admin-status-badge :status="$partylist->status->value" />
                </div>
                <p class="mt-3 text-sm text-slate-400">{{ $partylist->platform }}</p>

                <div class="mt-4 space-y-3">
                    @forelse ($partylist->posters as $poster)
                        @if ($poster->hasUploadedFile())
                            <div class="rounded-lg border border-slate-700 bg-slate-900/80 p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ $poster->file_url }}" target="_blank" rel="noopener" class="block shrink-0 overflow-hidden rounded-lg border border-slate-700">
                                        <img src="{{ $poster->file_url }}" alt="{{ $poster->title }}" class="h-20 w-20 object-cover" loading="lazy">
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-white">{{ $poster->title }}</p>
                                        @if ($poster->description)
                                            <p class="mt-1 text-xs text-slate-400">{{ $poster->description }}</p>
                                        @endif
                                        <p class="mt-1 text-[10px] text-slate-500">Uploaded {{ $poster->submitted_at?->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-slate-500">No posters uploaded for this partylist.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400 lg:col-span-2">No published campaigns yet. Set a campaign status to Published to display it here.</p>
        @endforelse
    </div>
</section>
