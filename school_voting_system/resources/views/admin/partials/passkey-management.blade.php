@php
    $passkeyStatus = $passkeyStatus ?? 'active';
    $currentPasskeyId = (int) ($currentPasskeyId ?? 0);
    $pendingRecoveries = (int) ($statistics['pending_recoveries'] ?? 0);
    $disabledPasskey = session('disabled_passkey');
    $filterBase = array_filter([
        'passkey_q' => request('passkey_q'),
        'passkey_role' => request('passkey_role'),
        'portal_q' => request('portal_q'),
    ], fn ($value) => filled($value));
    $statusLink = fn (string $status) => route('super-admin.dashboard', array_merge($filterBase, ['passkey_status' => $status])).'#passkey-management';
    $statusTab = fn (string $status) => 'rounded-lg px-3 py-1.5 text-xs font-semibold '.($passkeyStatus === $status
        ? 'bg-violet-600 text-white'
        : 'border border-slate-700 text-slate-300 hover:bg-slate-800');
    $statusBadge = function ($passkey) {
        $status = $passkey->status;
        $label = $status?->label() ?? 'Active';
        $class = match ($status?->value) {
            'revoked' => 'border-rose-500/30 bg-rose-500/10 text-rose-200',
            'lost' => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
            'expired' => 'border-slate-600 bg-slate-800/80 text-slate-300',
            default => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200',
        };

        return [$label, $class];
    };
@endphp

<section id="passkey-management" class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Advanced Passkey Management</h3>
            <p class="mt-1 max-w-2xl text-xs text-slate-500">
                Revoke disables this device. Mark lost does the same and records it as a lost device. Neither emails a new enrollment link.
            </p>
        </div>
        <a href="{{ route('admin.recovery.index') }}" class="shrink-0 text-xs font-semibold text-violet-300 hover:text-violet-200" data-recovery-queue-link>
            Recovery queue
            @if ($pendingRecoveries > 0)
                <span class="ml-1 rounded-full bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-bold text-amber-100">{{ $pendingRecoveries }}</span>
            @endif
        </a>
    </div>

    @if (is_array($disabledPasskey))
        @php
            $disabledUserId = (int) ($disabledPasskey['user_id'] ?? 0);
        @endphp
        <div class="mt-4 rounded-xl border border-cyan-500/25 bg-cyan-500/5 p-4" data-disabled-passkey-followup>
            <p class="text-sm font-medium text-cyan-100">This device can no longer sign in.</p>
            <p class="mt-1 text-xs text-slate-400">
                Issue a new enrollment if {{ $disabledPasskey['name'] ?? 'this person' }}
                ({{ $disabledPasskey['account_id'] ?? 'account' }}) still needs access.
            </p>
            @if (! empty($disabledPasskey['has_pending_recovery']))
                <p class="mt-2 text-xs text-amber-200">This account also has a pending request on the recovery queue.</p>
            @endif
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($disabledUserId)
                    <form method="POST" action="{{ route('admin.passkey.reset', $disabledUserId) }}" onsubmit="return confirm(@js('Generate a passkey reset / enrollment link for '.($disabledPasskey['name'] ?? 'this account').'? This emails the address on file.'));">
                        @csrf
                        <button type="submit" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500">Reset Passkey</button>
                    </form>
                    <a href="{{ $disabledPasskey['devices_url'] ?? '#passkey-management' }}" class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">
                        View devices
                    </a>
                @endif
                @if (! empty($disabledPasskey['has_pending_recovery']))
                    <a href="{{ route('admin.recovery.index') }}" class="rounded-lg border border-amber-400/30 px-3 py-1.5 text-xs font-semibold text-amber-100 hover:bg-amber-500/10">Open recovery queue</a>
                @endif
            </div>
        </div>
    @endif

    <form method="GET" action="{{ route('super-admin.dashboard') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
        @if (request()->filled('portal_q'))
            <input type="hidden" name="portal_q" value="{{ request('portal_q') }}">
        @endif
        <input type="hidden" name="passkey_status" value="{{ $passkeyStatus }}">
        <input
            name="passkey_q"
            type="search"
            value="{{ request('passkey_q') }}"
            placeholder="Search name, account ID, or email"
            class="w-full min-w-0 flex-1 rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-slate-100 sm:min-w-[14rem]"
        />
        <select name="passkey_role" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white sm:w-auto">
            <option value="">All roles</option>
            <option value="student" @selected(request('passkey_role') === 'student')>Student</option>
            <option value="faculty" @selected(request('passkey_role') === 'faculty')>Faculty</option>
            <option value="admin" @selected(request('passkey_role') === 'admin')>Administrator</option>
            <option value="super_admin" @selected(request('passkey_role') === 'super_admin')>Super Admin</option>
        </select>
        <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-xs font-semibold text-white sm:w-auto">Filter</button>
        @if (request()->filled('passkey_q') || request()->filled('passkey_role') || (request()->filled('passkey_status') && request('passkey_status') !== 'active'))
            <a href="{{ route('super-admin.dashboard', array_filter(['portal_q' => request('portal_q')])).'#passkey-management' }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-700 px-4 py-2 text-xs font-semibold text-slate-300 hover:bg-slate-800 sm:w-auto">Clear</a>
        @endif
    </form>

    <div class="mt-3 flex flex-wrap gap-2" role="tablist" aria-label="Passkey status">
        <a href="{{ $statusLink('active') }}" class="{{ $statusTab('active') }}">Active</a>
        <a href="{{ $statusLink('revoked') }}" class="{{ $statusTab('revoked') }}">Revoked</a>
        <a href="{{ $statusLink('lost') }}" class="{{ $statusTab('lost') }}">Marked lost</a>
        <a href="{{ $statusLink('all') }}" class="{{ $statusTab('all') }}">All</a>
    </div>

    <p class="mt-3 text-xs text-slate-500">{{ $passkeys->total() }} passkey{{ $passkeys->total() === 1 ? '' : 's' }} · default is Active only</p>

    <div class="mt-4 space-y-3 lg:hidden" data-passkey-cards>
        @forelse ($passkeys as $passkey)
            @php
                [$statusLabel, $badgeClass] = $statusBadge($passkey);
                $deviceLabel = $passkey->device_name ?? $passkey->name ?? 'Device';
                $platform = \App\Support\UserAgentParser::platformFromDeviceName($deviceLabel);
                $isCurrent = $currentPasskeyId > 0 && (int) $passkey->id === $currentPasskeyId;
            @endphp
            <article class="rounded-xl border border-slate-800 bg-slate-950/50 p-3">
                @if ($passkey->user)
                    <a href="{{ $passkey->user->adminDevicesUrl(auth()->user()) }}" class="break-words font-medium text-white hover:text-violet-200">{{ $passkey->user->name }}</a>
                    <p class="mt-0.5 font-mono text-xs text-violet-300">{{ $passkey->user->account_id }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $passkey->user->roleLabel() }}</p>
                @else
                    <p class="font-medium text-white">Unknown account</p>
                @endif
                @if ($isCurrent)
                    <p class="mt-2 text-[11px] font-semibold text-cyan-200">This device</p>
                @endif
                <dl class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
                    <div>
                        <dt class="uppercase tracking-wide text-slate-500">Device</dt>
                        <dd class="mt-0.5 break-words text-slate-200">{{ $deviceLabel }}@if ($platform !== 'Unknown') <span class="text-slate-500">({{ $platform }})</span> @endif</dd>
                    </div>
                    <div>
                        <dt class="uppercase tracking-wide text-slate-500">Status</dt>
                        <dd class="mt-0.5"><span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] {{ $badgeClass }}">{{ $statusLabel }}</span></dd>
                    </div>
                    <div>
                        <dt class="uppercase tracking-wide text-slate-500">Added</dt>
                        <dd class="mt-0.5 text-slate-200">{{ $passkey->created_at?->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="uppercase tracking-wide text-slate-500">Last used</dt>
                        <dd class="mt-0.5 text-slate-200" title="{{ $passkey->last_used_at?->format('M d, Y g:i A') }}">{{ $passkey->last_used_at?->diffForHumans() ?? 'Never' }}</dd>
                    </div>
                </dl>
                <div class="mt-3">
                    @include('admin.partials.passkey-row-actions', ['passkey' => $passkey, 'currentPasskeyId' => $currentPasskeyId, 'stacked' => true])
                </div>
            </article>
        @empty
            <p class="px-1 py-4 text-center text-sm text-slate-500">{{ request()->filled('passkey_q') || request()->filled('passkey_role') || $passkeyStatus !== 'active' ? 'No passkeys match these filters.' : 'No active passkeys registered yet.' }}</p>
        @endforelse
    </div>

    <div class="mt-4 hidden overflow-x-auto lg:block">
        <table class="min-w-full text-left text-xs sm:text-sm">
            <thead class="border-b border-slate-800 text-slate-400">
                <tr>
                    <th class="px-3 py-2">Account</th>
                    <th class="px-3 py-2">Device</th>
                    <th class="px-3 py-2">Added</th>
                    <th class="px-3 py-2">Last Used</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($passkeys as $passkey)
                    @php
                        [$statusLabel, $badgeClass] = $statusBadge($passkey);
                        $deviceLabel = $passkey->device_name ?? $passkey->name ?? 'Device';
                        $platform = \App\Support\UserAgentParser::platformFromDeviceName($deviceLabel);
                        $isCurrent = $currentPasskeyId > 0 && (int) $passkey->id === $currentPasskeyId;
                    @endphp
                    <tr class="text-slate-200">
                        <td class="px-3 py-2">
                            @if ($passkey->user)
                                <a href="{{ $passkey->user->adminDevicesUrl(auth()->user()) }}" class="font-medium text-white hover:text-violet-200">{{ $passkey->user->name }}</a>
                                <p class="font-mono text-[11px] text-violet-300">{{ $passkey->user->account_id }}</p>
                                <p class="text-[11px] text-slate-400">{{ $passkey->user->roleLabel() }}</p>
                                @if ($isCurrent)
                                    <p class="mt-1 text-[11px] font-semibold text-cyan-200">This device</p>
                                @endif
                            @else
                                Unknown account
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span>{{ $deviceLabel }}</span>
                            @if ($platform !== 'Unknown')
                                <span class="block text-[11px] text-slate-500">{{ $platform }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $passkey->created_at?->format('M d, Y') }}</td>
                        <td class="px-3 py-2" title="{{ $passkey->last_used_at?->format('M d, Y g:i A') }}">{{ $passkey->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-3 py-2">
                            @include('admin.partials.passkey-row-actions', ['passkey' => $passkey, 'currentPasskeyId' => $currentPasskeyId, 'stacked' => false])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-6 text-center text-slate-500">{{ request()->filled('passkey_q') || request()->filled('passkey_role') || $passkeyStatus !== 'active' ? 'No passkeys match these filters.' : 'No active passkeys registered yet.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($passkeys->hasPages())
        <div class="mt-4">{{ $passkeys->fragment('passkey-management')->links() }}</div>
    @endif
</section>
