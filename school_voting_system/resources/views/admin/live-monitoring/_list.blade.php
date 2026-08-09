<div class="hidden overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/80" data-live-list>
    <table class="min-w-full text-left text-sm">
        <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Owner</th>
                <th class="px-4 py-3">Status</th>
                @if (($mode ?? '') === 'election')
                    <th class="px-4 py-3 text-right">Votes</th>
                    <th class="px-4 py-3 text-right">Turnout</th>
                @else
                    <th class="px-4 py-3 text-right">Votes</th>
                    <th class="px-4 py-3 text-right">Approved</th>
                @endif
                <th class="px-4 py-3">Last Vote</th>
                <th class="px-4 py-3 text-right">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800" data-live-list-body>
            @foreach ($cards as $card)
                <tr class="text-slate-300" data-live-list-row data-card-id="{{ $card['id'] }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if (! empty($card['is_live']))
                                <span class="relative flex h-2 w-2 shrink-0">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-white" data-field="name">{{ $card['name'] }}</p>
                                @if (($mode ?? '') === 'talent')
                                    <p class="truncate text-[11px] text-violet-300" data-field="category">{{ $card['category'] ?? '' }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs" data-field="owner_name">{{ $card['owner_name'] }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[11px] font-semibold text-slate-200" data-field="status_label">{{ $card['status_label'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-bold text-white" data-field="votes_cast" data-flashable>{{ number_format($card['votes_cast'] ?? 0) }}</td>
                    @if (($mode ?? '') === 'election')
                        <td class="px-4 py-3 text-right text-violet-300"><span data-field="turnout_percent" data-flashable>{{ $card['turnout_percent'] ?? 0 }}</span>%</td>
                    @else
                        <td class="px-4 py-3 text-right text-violet-300" data-field="approved_participants" data-flashable>{{ number_format($card['approved_participants'] ?? 0) }}</td>
                    @endif
                    <td class="px-4 py-3 text-xs" data-field="last_vote_at" data-flashable>{{ $card['last_vote_at'] ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ $card['details_url'] }}" class="font-semibold text-violet-300 hover:text-violet-200">Open →</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
