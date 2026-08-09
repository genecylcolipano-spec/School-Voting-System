<x-app-layout>
    <x-admin-portal :title="$title" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $title,
            'description' => $description,
            'showAction' => false,
        ])

        @include('admin.live-monitoring._summary')
        @include('admin.live-monitoring._activity-feed', ['cards' => $cards])
        @include('admin.live-monitoring._filters')

        <div
            id="live-monitoring-root"
            data-poll-url="{{ $pollUrl }}"
            data-mode="talent"
            data-election-url="{{ route('admin.live.election') }}"
            data-talent-url="{{ route('admin.live.talent') }}"
            data-can-manage="{{ ($canManage ?? false) ? '1' : '0' }}"
            data-can-export="{{ ($canExport ?? false) ? '1' : '0' }}"
        >
            <div class="space-y-6" data-cards-view>
                @if ($urgentCards->isNotEmpty())
                    <section>
                        <h3 class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-emerald-300">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            </span>
                            Needs attention
                        </h3>
                        <div class="grid gap-5 lg:grid-cols-2" data-live-cards data-urgent-grid>
                            @foreach ($urgentCards as $card)
                                @include('admin.live-monitoring._talent-card', [
                                    'card' => $card,
                                    'canManage' => $canManage ?? false,
                                    'canExport' => $canExport ?? false,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($otherCards->isNotEmpty())
                    <section>
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Other activities</h3>
                        <div class="grid gap-5 lg:grid-cols-2" data-live-cards data-other-grid>
                            @foreach ($otherCards as $card)
                                @include('admin.live-monitoring._talent-card', [
                                    'card' => $card,
                                    'canManage' => $canManage ?? false,
                                    'canExport' => $canExport ?? false,
                                ])
                            @endforeach
                        </div>
                    </section>
                @endif

                <div class="grid gap-5 lg:grid-cols-2" data-live-cards-empty-host>
                    @include('admin.live-monitoring._empty')
                </div>
            </div>

            @include('admin.live-monitoring._list', ['mode' => 'talent', 'cards' => $cards])
        </div>
    </x-admin-portal>

    @vite(['resources/js/admin-live-monitoring.js'])
</x-app-layout>
