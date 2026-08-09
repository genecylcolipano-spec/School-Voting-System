<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">My Entries</h1>
                <p class="mt-1 text-sm text-slate-400">Track your talent competition submissions and review status.</p>
            </div>
            <a href="{{ route('student.talent-registration.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-800">
                Browse Competitions
            </a>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-left text-sm">
                    <thead class="bg-slate-950/50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Competition</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold">Entry Status</th>
                            <th class="px-4 py-3 font-semibold">Review Status</th>
                            <th class="px-4 py-3 font-semibold">Submitted</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($entries as $entry)
                            @php
                                $reviewLabel = $flow->reviewStatusLabel($entry);
                                $badge = match ($entry->status) {
                                    \App\Models\TalentEventEntry::STATUS_APPROVED => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-200',
                                    \App\Models\TalentEventEntry::STATUS_REJECTED => 'border-rose-500/30 bg-rose-500/15 text-rose-200',
                                    \App\Models\TalentEventEntry::STATUS_PENDING => 'border-amber-500/30 bg-amber-500/15 text-amber-200',
                                    default => 'border-slate-600/40 bg-slate-800/60 text-slate-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-950/40">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-white">{{ $entry->talentEvent?->title ?? '—' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $entry->entry_number ?: 'No entry number' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $entry->talent_category?->label() ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $badge }}">
                                        {{ $entry->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $reviewLabel }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ optional($entry->submitted_at)->format('M d, Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('student.talent-registration.entry.show', $entry) }}" class="text-xs font-semibold text-cyan-300 hover:text-cyan-200">View</a>
                                        <a href="{{ route('student.talent-registration.entry.confirmation', $entry) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-200">Download Confirmation</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                                    You have not submitted any talent competition entries yet.
                                    <a href="{{ route('student.talent-registration.index') }}" class="mt-2 block font-semibold text-cyan-300 hover:text-cyan-200">Browse competitions</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
