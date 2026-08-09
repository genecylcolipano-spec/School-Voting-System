@php
    $section = in_array($section ?? 'profile', ['profile', 'devices', 'security'], true)
        ? ($section ?? 'profile')
        : 'profile';
    $isSuperAdmin = $user->isSuperAdmin();
    $passwordlessEnabled = $passwordlessEnabled ?? ($user->passkeys_count > 0);
    $securityContext = $securityContext ?? [];
    $departmentLabel = $departmentLabel ?? ($user->staffRole?->name ?? ($isSuperAdmin ? 'System Administration' : '—'));
    $trustedDeviceCount = $trustedDeviceCount ?? 0;
    $systemAccessHistory = $systemAccessHistory ?? [];
    $navClass = fn (string $key) => 'rounded-xl px-4 py-2 text-sm font-semibold transition '.($section === $key
        ? 'bg-cyan-500/20 text-cyan-100 ring-1 ring-cyan-500/30'
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');
@endphp
<x-app-layout>
    <x-admin-portal title="Settings" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-400">
                {{ $isSuperAdmin ? 'Manage your super administrator profile, devices, and security posture.' : 'Manage your administrator profile, devices, and account security.' }}
            </p>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Settings sections">
            <a href="{{ route('profile.edit', ['section' => 'profile']) }}" class="{{ $navClass('profile') }}">Profile</a>
            <a href="{{ route('profile.edit', ['section' => 'devices']) }}" class="{{ $navClass('devices') }}">Devices</a>
            <a href="{{ route('profile.edit', ['section' => 'security']) }}" class="{{ $navClass('security') }}">Security</a>
        </nav>

        @if (session('status') === 'profile-updated')
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">Profile updated successfully.</div>
        @endif
        @if (session('status') === 'other-sessions-logged-out')
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">Other devices have been signed out.</div>
        @endif

        @if ($section === 'profile')
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Profile</h2>
                            <p class="mt-1 text-sm text-slate-400">Update your name, email, and profile picture.</p>
                        </div>
                        <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300">{{ $user->roleLabel() }}</span>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5" x-data="{ preview: @js($user->avatarUrl()), removeAvatar: false }">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-950">
                                <template x-if="preview && !removeAvatar">
                                    <img :src="preview" alt="Profile picture" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview || removeAvatar">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cyan-500 to-sky-400 text-2xl font-bold text-slate-950">{{ $user->initials() }}</div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="block text-sm font-medium text-slate-300">{{ $isSuperAdmin ? 'Avatar' : 'Profile Picture' }}</label>
                                <input
                                    type="file"
                                    name="avatar"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="mt-2 block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-950 hover:file:bg-cyan-400"
                                    @change="
                                        removeAvatar = false;
                                        const file = $event.target.files?.[0];
                                        preview = file ? URL.createObjectURL(file) : preview;
                                    "
                                />
                                <input type="hidden" name="remove_avatar" :value="removeAvatar ? 1 : 0">
                                @if ($user->avatarUrl())
                                    <button type="button" class="mt-2 text-xs font-semibold text-rose-300 hover:text-rose-200" @click="removeAvatar = true; preview = null">Remove photo</button>
                                @endif
                                @error('avatar')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-300">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            @error('name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            @error('email')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            @unless ($isSuperAdmin)
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Department</label>
                                    <input type="text" value="{{ $departmentLabel }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                            @endunless
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Role</label>
                                <input type="text" value="{{ $user->roleLabel() }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                        </div>

                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Save changes
                        </button>
                    </form>
                </section>

                @php
                    $adminSummaryRows = [
                        ['label' => 'Account ID', 'value' => $user->account_id ?? '—'],
                        ['label' => 'Role', 'value' => $user->roleLabel()],
                    ];
                    if (! $isSuperAdmin) {
                        $adminSummaryRows[] = ['label' => 'Department', 'value' => $departmentLabel];
                    }
                    $adminSummaryRows = array_merge($adminSummaryRows, [
                        ['label' => 'Registered Devices', 'value' => (string) $user->passkeys_count],
                        ['label' => 'Authentication', 'value' => 'Passwordless (Passkeys)', 'valueClass' => 'text-emerald-300'],
                        ['label' => 'Last Login', 'value' => optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—'],
                    ]);
                @endphp
                <x-settings.account-summary :rows="$adminSummaryRows" />
            </div>
        @endif

        @if ($section === 'devices')
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Register New Passkey</h2>
                    <p class="mt-1 text-sm text-slate-400">Add another trusted device for passwordless administrator sign-in.</p>
                    <div class="mt-5 [&_.rounded-xl]:border-cyan-500/20 [&_.rounded-xl]:bg-slate-950/50 [&_p]:text-slate-300 [&_label]:text-slate-300 [&_input]:border-slate-700 [&_input]:bg-slate-950 [&_input]:text-slate-100">
                        <x-passkey-register
                            :register-options-url="route('register.passkey.options')"
                            :register-verify-url="route('register.passkey.verify')"
                        />
                    </div>
                </section>

                <section class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Registered Devices</h2>
                    <p class="mt-1 text-sm text-slate-400">Credential IDs and public keys are never displayed.</p>

                    <ul
                        id="passkey-device-list"
                        class="mt-5 space-y-3"
                        data-index-url="{{ route('passkeys.index') }}"
                        data-update-url-template="{{ url('/user/passkeys/__ID__') }}"
                        data-csrf="{{ csrf_token() }}"
                    >
                        @forelse ($passkeys as $passkey)
                            <x-settings.device-card :passkey="$passkey" />
                        @empty
                            <li>
                                <x-settings.empty-state
                                    title="No authentication devices registered"
                                    description="Register a passkey to secure administrator access to the portal."
                                    action-label="Register Passkey"
                                    :action-href="route('profile.edit', ['section' => 'devices']).'#register-passkey-btn'"
                                />
                            </li>
                        @endforelse
                    </ul>
                </section>
            </div>
            @vite('resources/js/passkey-devices.js')
        @endif

        @if ($section === 'security')
            <div class="space-y-6">
                <x-settings.security-card
                    title="Authentication Status"
                    description="Passkey authentication is required for portal access."
                >
                    <x-slot:actions>
                        <span @class([
                            'rounded-full border px-3 py-1 text-xs font-semibold',
                            'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $passwordlessEnabled,
                            'border-amber-500/30 bg-amber-500/10 text-amber-200' => ! $passwordlessEnabled,
                        ])>
                            {{ $passwordlessEnabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </x-slot:actions>

                    <dl class="grid gap-3 text-sm sm:grid-cols-2 {{ $isSuperAdmin ? 'lg:grid-cols-4' : '' }}">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Require Passkey Authentication</dt>
                            <dd class="mt-1 font-medium text-emerald-300">Required</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login IP</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ $securityContext['ip'] ?? '—' }}</dd>
                        </div>
                        @if ($isSuperAdmin)
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Registered Devices</dt>
                                <dd class="mt-1 font-medium text-slate-200">{{ $user->passkeys_count }}</dd>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Trusted Devices</dt>
                                <dd class="mt-1 font-medium text-slate-200">{{ $trustedDeviceCount }}</dd>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                                <dt class="text-xs text-slate-500">Last Authentication</dt>
                                <dd class="mt-1 font-medium text-slate-200">{{ optional($lastAuthentication->last_used_at ?? null)->diffForHumans() ?? '—' }}</dd>
                            </div>
                        @endif
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 sm:col-span-2">
                            <dt class="text-xs text-slate-500">{{ $isSuperAdmin ? 'Emergency Recovery Email' : 'Recovery Email' }}</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ $user->email ?: '—' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ route('profile.edit', ['section' => 'devices']) }}" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Manage Passkeys
                        </a>
                    </div>
                </x-settings.security-card>

                <x-settings.security-card title="Active Sessions" description="Devices currently signed in with your administrator credentials.">
                    <x-slot:actions>
                        <form method="POST" action="{{ route('profile.logout-other-sessions') }}" onsubmit="return confirm('Sign out of every other device? You will stay signed in on this device.')">
                            @csrf
                            <button type="submit" class="rounded-xl border border-rose-500/30 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/10">
                                Logout Other Devices
                            </button>
                        </form>
                    </x-slot:actions>

                    <ul class="space-y-3">
                        @forelse ($activeSessions as $session)
                            <x-settings.session-card :session="$session" />
                        @empty
                            <li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-500">No active sessions found.</li>
                        @endforelse
                    </ul>
                </x-settings.security-card>

                <x-settings.security-card
                    title="{{ $isSuperAdmin ? 'Recent Login History' : 'Login History' }}"
                    description="{{ $isSuperAdmin ? 'Your most recent successful authentication events.' : 'Last 10 successful sign-ins to your account.' }}"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-slate-800 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-2 py-2 font-semibold">Date & Time</th>
                                    <th class="px-2 py-2 font-semibold">Browser</th>
                                    <th class="px-2 py-2 font-semibold">Device</th>
                                    <th class="px-2 py-2 font-semibold">IP</th>
                                    <th class="px-2 py-2 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse ($loginHistory as $entry)
                                    <tr>
                                        <td class="px-2 py-3 text-slate-300">{{ optional($entry['occurred_at'])->format('M d, Y g:i A') ?? '—' }}</td>
                                        <td class="px-2 py-3 text-slate-300">{{ $entry['browser'] }}</td>
                                        <td class="px-2 py-3 text-slate-300">{{ $entry['device'] }}</td>
                                        <td class="px-2 py-3 text-slate-300">{{ $entry['ip_address'] ?? '—' }}</td>
                                        <td class="px-2 py-3">
                                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">{{ $entry['status'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-2 py-4 text-slate-500">No login history available yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-settings.security-card>

                @if ($isSuperAdmin)
                    <x-settings.security-card
                        title="System Access History"
                        description="Recent privileged actions attributed to your super administrator account."
                    >
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-slate-800 text-xs uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-2 py-2 font-semibold">Date & Time</th>
                                        <th class="px-2 py-2 font-semibold">Action</th>
                                        <th class="px-2 py-2 font-semibold">Type</th>
                                        <th class="px-2 py-2 font-semibold">IP</th>
                                        <th class="px-2 py-2 font-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    @forelse ($systemAccessHistory as $entry)
                                        <tr>
                                            <td class="px-2 py-3 text-slate-300">{{ optional($entry['occurred_at'])->format('M d, Y g:i A') ?? '—' }}</td>
                                            <td class="px-2 py-3 text-slate-300">{{ $entry['action'] }}</td>
                                            <td class="px-2 py-3 text-slate-300">{{ $entry['type'] }}</td>
                                            <td class="px-2 py-3 text-slate-300">{{ $entry['ip_address'] ?? '—' }}</td>
                                            <td class="px-2 py-3">
                                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">{{ $entry['status'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-2 py-4 text-slate-500">No system access history available yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-settings.security-card>
                @endif
            </div>
        @endif
    </x-admin-portal>
</x-app-layout>
