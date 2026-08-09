@php
    use App\Support\EventImageUrl;
@endphp

<section>
    <div class="mb-4">
        <h2 class="text-xl font-bold text-white">Upcoming Activities</h2>
        <p class="mt-1 text-sm text-slate-400">Browse upcoming elections, school events, talent competitions, and fundraising campaigns available to you.</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-cyan-500/15 bg-slate-900/70 shadow-sm shadow-black/20">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] table-fixed border-collapse text-sm">
                <colgroup>
                    <col style="width: 72px">
                    <col>
                    <col style="width: 150px">
                    <col style="width: 170px">
                    <col style="width: 170px">
                    <col style="width: 140px">
                </colgroup>
                <thead class="border-b border-slate-800 text-slate-400">
                    <tr>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Banner</th>
                        <th class="h-12 px-3 py-3 text-left align-middle font-medium sm:px-4">Event</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Category</th>
                        <th class="h-12 px-3 py-3 text-left align-middle font-medium sm:px-4">Schedule</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Status</th>
                        <th class="h-12 px-2 py-3 text-center align-middle font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($upcomingSchedule as $item)
                        @php
                            $actionVariant = $item['action_style'] ?? ($item['action_disabled'] ? 'disabled' : 'secondary');
                            $actionDisabled = ($actionVariant === 'disabled')
                                || (bool) ($item['action_disabled'] ?? false)
                                || empty($item['action_url']);
                            $categoryCover = EventImageUrl::coverFor($item['category_key'] ?? null);
                            $bannerSrc = EventImageUrl::uploadedOrCover($item['banner_url'] ?? null, $item['category_key'] ?? null);
                        @endphp
                        <tr class="h-[72px] text-slate-200 transition hover:bg-slate-800/40">
                            <td class="px-2 py-3 align-middle">
                                <div class="flex h-10 w-full items-center justify-center">
                                    <img
                                        src="{{ $bannerSrc }}"
                                        alt=""
                                        class="h-10 w-10 shrink-0 rounded-lg object-cover ring-1 ring-slate-700"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ $categoryCover }}';"
                                    >
                                </div>
                            </td>
                            <td class="overflow-hidden px-3 py-3 text-left align-middle sm:px-4">
                                <span class="line-clamp-2 break-words font-medium leading-snug text-white" title="{{ $item['title'] }}">
                                    {{ $item['title'] }}
                                </span>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <x-ui.badge
                                        type="category"
                                        :tone-key="$item['category_key']"
                                        :label="$item['category']"
                                    />
                                </div>
                            </td>
                            <td class="px-3 py-3 text-left align-middle text-slate-400 sm:px-4">
                                <span class="block leading-snug" title="{{ $item['schedule_label'] }}">
                                    {{ $item['schedule_label'] }}
                                </span>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <x-ui.badge
                                        type="status"
                                        :tone-key="$item['status_key']"
                                        :label="$item['status_label']"
                                    />
                                </div>
                            </td>
                            <td class="px-2 py-3 align-middle">
                                <div class="flex w-full items-center justify-center">
                                    <x-ui.action-button
                                        :href="$actionDisabled ? null : $item['action_url']"
                                        :variant="$actionVariant"
                                        :disabled="$actionDisabled"
                                    >
                                        {{ $item['action_label'] }}
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <p class="text-sm font-semibold text-white">No upcoming activities</p>
                                <p class="mt-1 text-sm text-slate-500">There are currently no elections, events, competitions, or fundraising campaigns available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
