<x-app-layout>
    <x-faculty-portal title="Submitted Scores" :user="$user" :notifications-count="$notificationsCount">
        <section class="rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <p class="text-sm text-slate-400">Competition-level completion for your assigned judging work. Individual score sheets remain locked after submit.</p>
        </section>

        <div class="overflow-hidden rounded-2xl border border-teal-500/15 bg-slate-900/70">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-slate-950/60 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Competition</th>
                        <th class="px-4 py-3">Judge Role</th>
                        <th class="px-4 py-3">Participants Judged</th>
                        <th class="px-4 py-3">Completion %</th>
                        <th class="px-4 py-3">Submission Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($summaries as $row)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ $row['competition']->title }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $row['judge_role'] }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $row['participants_judged'] }} / {{ $row['participants_total'] }}</td>
                            <td class="px-4 py-3 font-semibold text-teal-200">{{ $row['completion_percent'] }}%</td>
                            <td class="px-4 py-3 text-slate-400">{{ optional($row['submission_date'])->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-teal-500/30 bg-teal-500/10 px-2.5 py-0.5 text-xs font-semibold text-teal-200">{{ $row['status'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('faculty.judging.show', $row['competition']) }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No submitted scores yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <section class="mt-6 rounded-2xl border border-teal-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Individual score sheets</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-800 text-sm">
                    <thead class="bg-slate-950/60 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Competition</th>
                            <th class="px-4 py-3">Performance</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($sheets as $sheet)
                            <tr>
                                <td class="px-4 py-3 text-slate-200">{{ $sheet->talentEvent?->title ?? '—' }}</td>
                                <td class="px-4 py-3 text-white">{{ $sheet->entry?->display_name ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-teal-200">{{ number_format((float) $sheet->total_score, 2) }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ optional($sheet->submitted_at)->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($sheet->talentEvent && $sheet->entry)
                                        <a href="{{ route('faculty.judging.score', [$sheet->talentEvent, $sheet->entry]) }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">No individual sheets yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $sheets->links() }}</div>
        </section>
    </x-faculty-portal>
</x-app-layout>
