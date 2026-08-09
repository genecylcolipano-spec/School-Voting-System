<x-app-layout>
    <x-admin-portal
        :title="'Judges — '.$talentEvent->title"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.talent-competition.show', $talentEvent) }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to competition</a>
            <span class="rounded-full border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-100">{{ $talentEvent->votingMethodLabel() }}</span>
        </div>

        @unless ($talentEvent->requiresJudges())
            <div class="mb-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                This competition uses student-only voting. Change the voting method to <strong>Judges Only</strong> or <strong>Judges + Students</strong> in Settings before judges can be assigned.
            </div>
        @endunless

        @unless ($canAssignJudges)
            <div class="mb-4 rounded-xl border border-cyan-500/25 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-100">
                Judge assignment is managed by the Super Administrator (Faculty → Assign Judges). Operations Admins can configure scoring criteria here.
            </div>
        @endunless

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Assigned judges</h2>
                <p class="mt-1 text-sm text-slate-400">Faculty accounts who can score performances in My Judging.</p>

                <ul class="mt-4 space-y-3">
                    @forelse ($talentEvent->judges as $assignment)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-3">
                            <div>
                                <p class="font-medium text-white">{{ $assignment->user?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $assignment->user?->account_id }}
                                    · {{ $assignment->roleLabel() }}
                                    · {{ $assignment->statusLabel() }}
                                </p>
                            </div>
                            @if ($canAssignJudges)
                                <form method="POST" action="{{ route('admin.talent-competition.judges.remove', [$talentEvent, $assignment->user]) }}" onsubmit="return confirm('Remove this judge assignment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-rose-300 hover:text-rose-200">Remove</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-700 px-4 py-6 text-center text-sm text-slate-500">No judges assigned yet.</li>
                    @endforelse
                </ul>

                @if ($canAssignJudges && $talentEvent->requiresJudges())
                    <form method="POST" action="{{ route('admin.talent-competition.judges.assign', $talentEvent) }}" class="mt-5 space-y-3 border-t border-slate-800 pt-5">
                        @csrf
                        <label class="block text-sm font-medium text-slate-200">Assign faculty</label>
                        <select name="user_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <option value="">Select faculty account…</option>
                            @foreach ($availableFaculty as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->account_id }})</option>
                            @endforeach
                        </select>
                        <label class="block text-sm font-medium text-slate-200">Judge role</label>
                        <select name="judge_role" required class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            @foreach ($judgeRoles as $role)
                                <option value="{{ $role->value }}" @selected($role === \App\Enums\TalentJudgeRole::Judge)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @if ($availableFaculty->isEmpty())
                            <p class="text-xs text-slate-500">No available faculty with an active account and registered Passkey.</p>
                        @endif
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white" @disabled($availableFaculty->isEmpty())>
                            Assign judge
                        </button>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Scoring criteria</h2>
                <p class="mt-1 text-sm text-slate-400">Default rubric totals 100 points. Criteria lock after scoring begins.</p>

                <form method="POST" action="{{ route('admin.talent-competition.criteria.update', $talentEvent) }}" class="mt-4 space-y-3" x-data="{
                    rows: @js($talentEvent->judgingCriteria->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'max_points' => $c->max_points])->values())
                }">
                    @csrf
                    @method('PUT')

                    <template x-for="(row, index) in rows" :key="index">
                        <div class="grid gap-2 rounded-xl border border-slate-800 bg-slate-950/40 p-3 sm:grid-cols-[1fr_100px_auto]">
                            <input type="hidden" :name="`criteria[${index}][id]`" :value="row.id || ''">
                            <input type="text" :name="`criteria[${index}][name]`" x-model="row.name" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Criterion name">
                            <input type="number" min="1" max="100" :name="`criteria[${index}][max_points]`" x-model.number="row.max_points" required class="rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Max">
                            <button type="button" @click="rows.splice(index, 1)" class="text-sm text-rose-300 hover:text-rose-200">Remove</button>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="rows.push({ id: null, name: '', max_points: 25 })" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                            Add criterion
                        </button>
                        @if ($canManageCriteria)
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">
                                Save criteria
                            </button>
                        @endif
                    </div>
                </form>
            </section>
        </div>
    </x-admin-portal>
</x-app-layout>
