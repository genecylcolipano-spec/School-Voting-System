<x-app-layout>
    <x-admin-portal :title="$account->name" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ $indexRoute }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to list</a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $editRoute }}" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
                <form method="POST" action="{{ route('super-admin.staff.enrollment', $account) }}" onsubmit="return confirm('Generate a passkey reset / enrollment link?');">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset Passkey</button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @include('admin.partials.enrollment-link-banner')

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-mono text-xs text-slate-500">{{ $account->account_id }}</p>
                    <h2 class="mt-1 text-2xl font-bold text-white">{{ $account->name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $account->email }} · {{ $account->roleLabel() }}</p>
                    @if ($account->staffRole)
                        <p class="mt-1 text-sm text-slate-400">Staff role: {{ $account->staffRole->name }}</p>
                    @endif
                </div>
                <div class="text-right">
                    @if ($account->archived_at)
                        <span class="rounded-full border border-slate-600 bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300">Archived</span>
                    @elseif ($account->is_active)
                        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-200">Active</span>
                    @else
                        <span class="rounded-full border border-rose-500/30 bg-rose-500/10 px-3 py-1 text-xs font-semibold text-rose-200">Inactive</span>
                    @endif
                    <p class="mt-2 text-xs text-slate-500">{{ $account->passkeys_count }} registered device(s)</p>
                </div>
            </div>

            @if ($removalBlockers !== [])
                <div class="mt-4 rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    <p class="font-semibold">Removal blocked — prefer deactivate/archive:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($removalBlockers as $blocker)
                            <li>{{ $blocker }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>

        @if ($account->isFaculty())
            <section id="competitions" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6" x-data="{ removeOpen: false, removeAction: '', removeTitle: '' }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Assigned Competitions</h3>
                        <p class="mt-1 text-sm text-slate-400">Super Admin assigns faculty judges after an Administrator creates the competition.</p>
                    </div>
                    @if (($account->passkeys_count ?? 0) < 1)
                        <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-200">Passkey required to assign</span>
                    @endif
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-2 py-2">Competition</th>
                                <th class="px-2 py-2">Category</th>
                                <th class="px-2 py-2">Judge Role</th>
                                <th class="px-2 py-2">Status</th>
                                <th class="px-2 py-2">Assigned</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assignedCompetitions as $assignment)
                                @php
                                    $event = $assignment->talentEvent;
                                    $eventUsable = $event && ! $event->trashed();
                                @endphp
                                <tr class="border-b border-slate-800/70 text-slate-200">
                                    <td class="px-2 py-3 font-medium text-white">
                                        {{ $event?->title ?? 'Unavailable competition' }}
                                        @if ($event?->trashed())
                                            <span class="ml-1 text-xs font-normal text-amber-300">(Archived)</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-3 text-slate-300">{{ $event?->talent_category?->label() ?? $event?->type?->label() ?? '—' }}</td>
                                    <td class="px-2 py-3">
                                        @if ($eventUsable)
                                            <form method="POST" action="{{ route('super-admin.faculty.competitions.role', [$account, $event->getKey()]) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <select name="judge_role" class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-white" onchange="this.form.submit()">
                                                    @foreach ($judgeRoles as $role)
                                                        <option value="{{ $role->value }}" @selected($assignment->judge_role === $role)>{{ $role->label() }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">{{ $assignment->roleLabel() }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-3">
                                        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">{{ $assignment->statusLabel() }}</span>
                                    </td>
                                    <td class="px-2 py-3 text-slate-400">{{ optional($assignment->assigned_at)->format('M d, Y') ?? '—' }}</td>
                                    <td class="px-2 py-3">
                                        <div class="flex flex-wrap gap-3">
                                            @if ($eventUsable)
                                                <a href="{{ route('admin.talent-competition.show', $event) }}" class="text-xs font-semibold text-violet-300 hover:text-violet-200">View Competition</a>
                                            @endif
                                            @if ($event || $assignment->talent_event_id)
                                                <button
                                                    type="button"
                                                    class="text-xs font-semibold text-rose-300 hover:text-rose-200"
                                                    @click="removeOpen = true; removeAction = @js(route('super-admin.faculty.competitions.remove', [$account, $event?->getKey() ?? $assignment->talent_event_id])); removeTitle = @js($event?->title ?? 'Unavailable competition')"
                                                >Remove Assignment</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-2 py-8 text-center text-sm text-slate-500">No competitions assigned.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('super-admin.faculty.competitions.assign', $account) }}" class="mt-5 grid gap-3 border-t border-slate-800 pt-5 sm:grid-cols-2 lg:grid-cols-4">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">Competition</label>
                        <select name="talent_event_id" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            <option value="">Select competition…</option>
                            @foreach ($assignableCompetitions as $competition)
                                <option value="{{ $competition->id }}">
                                    {{ $competition->title }}
                                    — {{ $competition->talent_category?->label() ?? $competition->type?->label() ?? 'Talent Competition' }}
                                    — {{ $competition->schoolYearLabel() }}
                                    — {{ $competition->assignmentPhaseLabel() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Faculty</label>
                        <input type="text" value="{{ $account->name }}" disabled class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/60 px-3 py-2 text-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Judge Role</label>
                        <select name="judge_role" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-white">
                            @foreach ($judgeRoles as $role)
                                <option value="{{ $role->value }}" @selected($role === \App\Enums\TalentJudgeRole::Judge)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4">
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white" @disabled($assignableCompetitions->isEmpty() || ($account->passkeys_count ?? 0) < 1)>
                            Assign Judge
                        </button>
                        @if ($assignableCompetitions->isEmpty())
                            <p class="mt-2 text-xs text-slate-500">No eligible competitions available. Published, active competitions created by an Administrator will appear here (excluding archived, completed, and already-assigned events).</p>
                        @elseif (($account->passkeys_count ?? 0) < 1)
                            <p class="mt-2 text-xs text-amber-300/90">This faculty account must register a Passkey before a judge assignment can be saved.</p>
                        @endif
                    </div>
                </form>

                <div x-show="removeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4" role="dialog" aria-modal="true">
                    <div class="w-full max-w-md rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl" @click.outside="removeOpen = false">
                        <h4 class="text-lg font-semibold text-white">Remove judge assignment?</h4>
                        <p class="mt-2 text-sm text-slate-400">
                            <span x-text="removeTitle"></span> — {{ $account->name }} will immediately lose judging access.
                        </p>
                        <form method="POST" :action="removeAction" class="mt-5 space-y-3">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Reason (optional)</label>
                                <textarea name="removal_reason" rows="2" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white" placeholder="Optional reason for the audit log"></textarea>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="removeOpen = false" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</button>
                                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Remove Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        <section id="devices" class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Registered Devices</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">Name</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-2 py-2">Last used</th>
                            <th class="px-2 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2">{{ $device->device_name ?: $device->name }}</td>
                                <td class="px-2 py-2">{{ $device->status?->value ?? $device->status ?? 'active' }}</td>
                                <td class="px-2 py-2 text-slate-400">{{ optional($device->last_used_at)->format('M d, Y g:i A') ?? '—' }}</td>
                                <td class="px-2 py-2 text-slate-400">{{ optional($device->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-2 py-6 text-center text-slate-500">No registered passkey devices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="login-history" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h3 class="text-lg font-semibold text-white">Login History</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="px-2 py-2">When</th>
                            <th class="px-2 py-2">Browser</th>
                            <th class="px-2 py-2">OS</th>
                            <th class="px-2 py-2">IP</th>
                            <th class="px-2 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loginHistory as $row)
                            <tr class="border-b border-slate-800/70 text-slate-200">
                                <td class="px-2 py-2 text-slate-400">{{ optional($row['occurred_at'])->format('M d, Y g:i A') }}</td>
                                <td class="px-2 py-2">{{ $row['browser'] }}</td>
                                <td class="px-2 py-2">{{ $row['os'] }}</td>
                                <td class="px-2 py-2 font-mono text-xs">{{ $row['ip_address'] ?? '—' }}</td>
                                <td class="px-2 py-2">{{ $row['status'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-2 py-6 text-center text-slate-500">No login history available yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </x-admin-portal>
</x-app-layout>
