@php
    $section = in_array($section ?? 'profile', ['profile', 'devices', 'security'], true)
        ? ($section ?? 'profile')
        : 'profile';
    $portalComponent = $portalComponent ?? 'student-portal';
    $isFacultyPortal = $portalComponent === 'faculty-portal';
    $isStudentProfile = $user->isStudent();
    $accentBorder = $isFacultyPortal ? 'border-teal-500/15' : 'border-cyan-500/15';
    $accent = $isFacultyPortal ? 'teal' : 'cyan';
    $accountStatus = $accountStatus ?? (filled($user->archived_at ?? null) ? 'Archived' : ($user->is_active ? 'Active' : 'Inactive'));
    $passwordlessEnabled = $passwordlessEnabled ?? ($user->passkeys_count > 0);
    $securityContext = $securityContext ?? [];
    $navClass = fn (string $key) => 'rounded-xl px-4 py-2 text-sm font-semibold transition '.($section === $key
        ? ($isFacultyPortal
            ? 'bg-teal-500/20 text-teal-100 ring-1 ring-teal-500/30'
            : 'bg-cyan-500/20 text-cyan-100 ring-1 ring-cyan-500/30')
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');

    $summaryRows = [
        ['label' => $isStudentProfile ? 'Student ID' : 'Account ID', 'value' => $user->account_id ?? '—'],
    ];

    if ($isFacultyPortal) {
        $summaryRows[] = [
            'label' => 'Assigned Competitions',
            'value' => ($user->judging_assignments_count ?? 0) > 0
                ? (string) $user->judging_assignments_count
                : 'None Assigned',
        ];
    } elseif ($isStudentProfile) {
        $summaryRows[] = [
            'label' => 'Grade & Section',
            'value' => trim(($user->grade_level ?: '—').' · '.($user->section ?: '—')),
        ];
    }

    $summaryRows = array_merge($summaryRows, [
        ['label' => 'Registered Devices', 'value' => (string) $user->passkeys_count],
        ['label' => 'Authentication Method', 'value' => 'Passwordless (Passkeys)', 'valueClass' => 'text-emerald-300'],
        [
            'label' => 'Last Login',
            'value' => optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—',
        ],
        [
            'label' => 'Member Since',
            'value' => optional($user->created_at)->format('M d, Y') ?? '—',
        ],
    ]);

    if ($isFacultyPortal) {
        $summaryRows[] = [
            'label' => 'Account Status',
            'value' => $accountStatus,
            'valueClass' => match ($accountStatus) {
                'Active' => 'text-emerald-300',
                'Inactive' => 'text-amber-300',
                default => 'text-slate-400',
            },
        ];
    }
@endphp
<x-app-layout>
    <x-dynamic-component :component="$portalComponent" title="Settings" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-6">
            <h1 class="text-xl font-bold text-white">Settings</h1>
            <p class="mt-1 text-sm text-slate-400">Manage your profile, authentication devices, and account security.</p>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Settings sections">
            <a href="{{ route('profile.edit', ['section' => 'profile']) }}" class="{{ $navClass('profile') }}">My Profile</a>
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
                <section class="rounded-2xl border {{ $accentBorder }} bg-slate-900/70 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">My Profile</h2>
                            <p class="mt-1 text-sm text-slate-400">Update your personal account details.</p>
                        </div>
                        <span class="rounded-full border border-slate-700 bg-slate-950/50 px-3 py-1 text-xs font-semibold text-slate-300">{{ $user->roleLabel() }}</span>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-5" x-data="{ preview: @js($user->avatarUrl()), removeAvatar: false }">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-700 bg-slate-950">
                                <template x-if="preview && !removeAvatar">
                                    <img :src="preview" alt="Profile photo" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!preview || removeAvatar">
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-cyan-500 to-sky-400 text-2xl font-bold text-slate-950">{{ $user->initials() }}</div>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="block text-sm font-medium text-slate-300">Profile Photo</label>
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
                                <p class="mt-1 text-xs text-slate-500">JPG, PNG, or WEBP. Max 2 MB.</p>
                                @error('avatar')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            @error('name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-300">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" />
                            @error('email')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-300">Phone Number <span class="text-slate-500">(optional)</span></label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100" placeholder="+63…" />
                            @error('phone')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>

                        @if ($isStudentProfile)
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Student ID</label>
                                    <input type="text" value="{{ $user->account_id ?: '—' }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Course</label>
                                    <input type="text" value="—" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Grade</label>
                                    <input type="text" value="{{ $user->grade_level ?: '—' }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-300">Section</label>
                                    <input type="text" value="{{ $user->section ?: '—' }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                                </div>
                            </div>
                            <p class="text-xs text-slate-500">Student ID, grade, section, and course are managed by the school administration.</p>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Role</label>
                                <input type="text" value="{{ $user->roleLabel() }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Account Status</label>
                                <input type="text" value="{{ $accountStatus }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Account Created</label>
                                <input type="text" value="{{ optional($user->created_at)->format('M d, Y') ?? '—' }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Last Login</label>
                                <input type="text" value="{{ optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—' }}" readonly class="mt-1 w-full cursor-not-allowed rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2 text-slate-400" />
                            </div>
                        </div>

                        <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Save Profile
                        </button>
                    </form>
                </section>

                <x-settings.account-summary :rows="$summaryRows" :border-class="$accentBorder" />
            </div>
        @endif

        @if ($section === 'devices')
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="rounded-2xl border {{ $accentBorder }} bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Register New Passkey</h2>
                    <p class="mt-1 text-sm text-slate-400">Add another device for passwordless sign-in. Credential secrets are never stored or displayed.</p>
                    <div class="mt-5 [&_.rounded-xl]:border-cyan-500/20 [&_.rounded-xl]:bg-slate-950/50 [&_p]:text-slate-300 [&_label]:text-slate-300 [&_input]:border-slate-700 [&_input]:bg-slate-950 [&_input]:text-slate-100">
                        <x-passkey-register
                            :register-options-url="route('register.passkey.options')"
                            :register-verify-url="route('register.passkey.verify')"
                        />
                    </div>
                </section>

                <section class="rounded-2xl border {{ $accentBorder }} bg-slate-900/70 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Registered Devices</h2>
                    <p class="mt-1 text-sm text-slate-400">Only device metadata is shown. You must keep at least one device registered.</p>

                    <ul
                        id="passkey-device-list"
                        class="mt-5 space-y-3"
                        data-index-url="{{ route('passkeys.index') }}"
                        data-update-url-template="{{ url('/user/passkeys/__ID__') }}"
                        data-csrf="{{ csrf_token() }}"
                    >
                        @forelse ($passkeys as $passkey)
                            <x-settings.device-card :passkey="$passkey" :accent="$accent" />
                        @empty
                            <li>
                                <x-settings.empty-state
                                    title="No authentication devices registered"
                                    description="Register a passkey on this device to enable secure passwordless sign-in."
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
                    title="Passwordless Authentication Status"
                    description="Your account uses passkeys for passwordless access."
                    :border-class="$accentBorder"
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

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('profile.edit', ['section' => 'devices']) }}" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:opacity-90">
                            Register Passkey
                        </a>
                        <a href="{{ route('profile.edit', ['section' => 'devices']) }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-800/70">
                            Manage Passkeys
                        </a>
                    </div>

                    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ optional($securityContext['at'] ?? null)->format('M d, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Last Login IP</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ $securityContext['ip'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Browser Used</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ $securityContext['browser'] ?? '—' }}</dd>
                        </div>
                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                            <dt class="text-xs text-slate-500">Operating System</dt>
                            <dd class="mt-1 font-medium text-slate-200">{{ $securityContext['os'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-settings.security-card>

                <x-settings.security-card
                    title="Active Sessions"
                    description="Devices currently signed in to your account."
                    :border-class="$accentBorder"
                >
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

                @if ($isStudentProfile)
                    <div
                        x-data="{ confirmDelete: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }"
                        class="rounded-2xl border border-rose-500/25 bg-slate-900/70 p-5 sm:p-6"
                    >
                        <h2 class="text-lg font-semibold text-white">Danger Zone</h2>
                        <p class="mt-1 text-sm text-slate-400">Permanently delete your student account and associated portal data.</p>

                        <button
                            type="button"
                            class="mt-5 rounded-xl border border-rose-500/40 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-200 hover:bg-rose-500/20"
                            @click="confirmDelete = true"
                        >
                            Delete Account
                        </button>

                        <div
                            x-show="confirmDelete"
                            x-cloak
                            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4"
                            @keydown.escape.window="confirmDelete = false"
                        >
                            <form method="post" action="{{ route('profile.destroy') }}" class="w-full max-w-md space-y-4 rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-xl" @click.outside="confirmDelete = false">
                                @csrf
                                @method('delete')

                                <h3 class="text-lg font-semibold text-white">Delete your account?</h3>
                                <p class="text-sm text-slate-400">
                                    This action cannot be undone. Type <span class="font-semibold text-rose-300">DELETE</span> to confirm.
                                </p>

                                <div>
                                    <label for="confirmation" class="block text-sm font-medium text-slate-300">Confirmation</label>
                                    <input
                                        id="confirmation"
                                        name="confirmation"
                                        type="text"
                                        autocomplete="off"
                                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2 text-slate-100"
                                        placeholder="DELETE"
                                    />
                                    <x-input-error :messages="$errors->userDeletion->get('confirmation')" class="mt-2" />
                                </div>

                                <div class="flex justify-end gap-3">
                                    <button type="button" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200" @click="confirmDelete = false">
                                        Cancel
                                    </button>
                                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                                        Delete Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </x-dynamic-component>
</x-app-layout>
