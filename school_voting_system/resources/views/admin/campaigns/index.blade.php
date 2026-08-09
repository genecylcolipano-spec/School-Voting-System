<x-app-layout>
    <x-admin-portal title="Campaigns & Partylists" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Campaigns & Partylists',
            'description' => 'Create reusable campaigns and attach them to elections during setup.',
            'action' => route('admin.campaigns.create'),
            'actionLabel' => 'Add campaign',
            'showAction' => $canManage,
        ])

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse ($partylists as $partylist)
                <article class="overflow-hidden rounded-2xl border border-violet-500/15 bg-slate-900/70">
                    @if ($partylist->bannerUrl())
                        <div @class([
                            'relative overflow-hidden bg-slate-950',
                            'aspect-[16/5] w-full' => ! $partylist->isPortraitBanner(),
                            'aspect-[3/4] max-h-40 w-full' => $partylist->isPortraitBanner(),
                        ])>
                            @include('student.campaigns._banner-media', [
                                'url' => $partylist->bannerUrl(),
                                'alt' => $partylist->name.' banner',
                                'portrait' => $partylist->isPortraitBanner(),
                            ])
                        </div>
                    @else
                        <div class="aspect-[16/5] w-full bg-gradient-to-br from-slate-900 via-slate-950 to-violet-950/40"></div>
                    @endif
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                @if ($partylist->logo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($partylist->logo_path) }}" alt="{{ $partylist->name }} logo" class="h-12 w-12 rounded-lg border border-slate-700 object-cover">
                                @endif
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $partylist->name }}</h3>
                                    @if ($partylist->acronym)
                                        <p class="text-sm text-violet-300">{{ $partylist->acronym }}</p>
                                    @endif
                                    @if ($partylist->motto)
                                        <p class="mt-1 text-xs italic text-slate-500">"{{ $partylist->motto }}"</p>
                                    @endif
                                </div>
                            </div>
                            <x-admin-status-badge :status="$partylist->status->value" />
                        </div>

                        @if ($partylist->platform)
                            <p class="mt-3 text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($partylist->platform, 160) }}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-500">
                            <span>{{ $partylist->elections_count }} election(s)</span>
                            <span>{{ $partylist->candidates_count }} candidate(s)</span>
                            <span>{{ $partylist->posters_count }} poster(s)</span>
                            @if ($partylist->leader)
                                <span>Leader: {{ $partylist->leader }}</span>
                            @endif
                        </div>

                        <div class="mt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Posters</p>

                            @if ($partylist->posters->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($partylist->posters as $poster)
                                        <a
                                            href="{{ $poster->hasUploadedFile() ? $poster->file_url : '#' }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="group relative overflow-hidden rounded-lg border border-slate-800"
                                            title="{{ ucfirst($poster->status) }}"
                                        >
                                            @if ($poster->hasUploadedFile())
                                                <img src="{{ $poster->file_url }}" alt="{{ $partylist->name }} poster" class="h-24 w-auto max-w-[6rem] rounded object-contain transition group-hover:opacity-90">
                                            @else
                                                <div class="flex h-24 w-24 items-center justify-center bg-slate-950 text-xs text-slate-500">No file</div>
                                            @endif
                                            <span class="absolute bottom-1 right-1 rounded bg-black/70 px-1.5 py-0.5 text-[10px] text-white">{{ $poster->status }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-slate-500">No poster uploaded yet.</p>
                            @endif

                            @can('update', $partylist)
                                <form
                                    method="POST"
                                    action="{{ route('admin.campaigns.poster.store', $partylist) }}"
                                    enctype="multipart/form-data"
                                    class="mt-3 flex flex-wrap items-center gap-2"
                                >
                                    @csrf
                                    <label class="flex-1 min-w-[12rem]">
                                        <span class="sr-only">Poster image for {{ $partylist->name }}</span>
                                        <input
                                            type="file"
                                            name="poster_image"
                                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                                            required
                                            class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-xs text-slate-100 file:mr-2 file:rounded-lg file:border-0 file:bg-violet-500/20 file:px-2 file:py-1 file:text-xs file:text-violet-300"
                                        >
                                    </label>
                                    <button type="submit" class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white hover:bg-violet-500">
                                        Upload poster
                                    </button>
                                </form>
                                <p class="mt-1 text-[10px] text-slate-500">Portrait or landscape JPG/PNG, max 2MB. Available once attached to an election.</p>
                            @endcan
                        </div>

                        <div class="mt-4 flex flex-wrap gap-3 text-sm">
                            @can('update', $partylist)
                                <a href="{{ route('admin.campaigns.edit', $partylist) }}" class="text-violet-300 hover:text-violet-200">Edit details</a>
                            @endcan
                            @can('delete', $partylist)
                                <form method="POST" action="{{ route('admin.campaigns.destroy', $partylist) }}" class="inline" onsubmit="return confirm('Delete this campaign?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-300 hover:text-rose-200">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </article>
            @empty
                <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/70 px-6 py-8 text-center text-slate-400">
                    No campaigns yet.
                    @if ($canManage)
                        <a href="{{ route('admin.campaigns.create') }}" class="ml-1 text-violet-300 hover:text-violet-200">Add your first campaign</a>
                    @endif
                </div>
            @endforelse
        </div>

        <p class="mt-6 text-xs text-slate-500">Active campaigns can be attached to elections during election setup and appear on the student portal automatically.</p>
    </x-admin-portal>
</x-app-layout>
