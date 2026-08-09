<x-app-layout>
    <x-admin-portal title="System Settings" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'System Settings',
            'description' => 'Global application configuration for the school voting platform.',
            'showAction' => false,
        ])

        <form method="POST" action="{{ route('super-admin.system.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">General</h2>
                <p class="mt-1 text-sm text-slate-400">Product name, school identity, and academic period.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">System Name</label>
                        <input name="system_name" type="text" value="{{ old('system_name', $settings['system_name']) }}"
                            placeholder="School Voting System"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-500">Shown as the product name in portals and page titles.</p>
                        @error('system_name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">School Name</label>
                        <input name="school_name" type="text" value="{{ old('school_name', $settings['school_name']) }}"
                            placeholder="Rosemont Hills Montessori College"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                        <p class="mt-1 text-xs text-slate-500">Shown as “Powered by …” under the system name.</p>
                        @error('school_name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">School Logo</label>
                        <div class="mt-2 flex flex-wrap items-center gap-4">
                            @if ($logoUrl)
                                <div>
                                    <img src="{{ $logoUrl }}" alt="School logo" class="h-16 w-16 rounded-xl border border-slate-700 object-cover">
                                    <p class="mt-1 text-center text-[10px] text-emerald-400/90">Custom upload</p>
                                </div>
                            @else
                                <div>
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl border border-slate-700 bg-gradient-to-br from-violet-600 to-indigo-500 text-white" aria-hidden="true">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <p class="mt-1 text-center text-[10px] text-slate-500">Default icon</p>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <input type="file" name="school_logo" accept="image/jpeg,image/png,image/webp"
                                    class="block w-full text-sm text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                                <p class="mt-1 text-xs text-slate-500">If nothing is uploaded, portals use the purple book icon.</p>
                                @if ($logoUrl)
                                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-400">
                                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-600 bg-slate-900 text-violet-500">
                                        Remove current logo
                                    </label>
                                @endif
                            </div>
                        </div>
                        @error('school_logo')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Academic Year</label>
                        <input name="academic_year" type="text" value="{{ old('academic_year', $settings['academic_year']) }}"
                            placeholder="2025-2026"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Semester</label>
                        <input name="semester" type="text" value="{{ old('semester', $settings['semester']) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Registration</h2>
                <p class="mt-1 text-sm text-slate-400">Control how accounts can be created.</p>
                <div class="mt-5 space-y-4">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="enable_student_registration" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('enable_student_registration', $settings['enable_student_registration']))>
                        <span>
                            <span class="font-medium text-white">Enable Student Registration</span>
                            <span class="mt-0.5 block text-slate-500">Users can register after matching Student, Faculty, or Administrator Roster records.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="enable_faculty_registration" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('enable_faculty_registration', $settings['enable_faculty_registration']))>
                        <span>
                            <span class="font-medium text-white">Enable Faculty Registration</span>
                            <span class="mt-0.5 block text-slate-500">Future-ready. Faculty accounts are still created by Super Admin today.</span>
                        </span>
                    </label>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
                        <p class="text-sm font-medium text-white">Passwordless Authentication Status</p>
                        <p class="mt-1 text-sm text-emerald-300">Enabled (Passkeys) — read-only</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Voting</h2>
                <p class="mt-1 text-sm text-slate-400">Module availability flags for the platform.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        'enable_elections' => ['Enable Elections', 'Student election ballots'],
                        'enable_talent_voting' => ['Enable Talent Competition Voting', 'Talent voting & judging'],
                        'enable_fundraising' => ['Enable Fundraising', 'Donation campaigns'],
                    ] as $field => [$label, $hint])
                        <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                            <input type="checkbox" name="{{ $field }}" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old($field, $settings[$field]))>
                            <span>
                                <span class="font-medium text-white">{{ $label }}</span>
                                <span class="mt-0.5 block text-slate-500">{{ $hint }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Announcements</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Default Visibility</label>
                        <select name="announcement_default_visibility" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                            @foreach (['all' => 'All users', 'students' => 'Students', 'faculty' => 'Faculty', 'admins' => 'Administrators'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('announcement_default_visibility', $settings['announcement_default_visibility']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Default Expiration (days)</label>
                        <input name="announcement_default_expiration_days" type="number" min="1" max="365"
                            value="{{ old('announcement_default_expiration_days', $settings['announcement_default_expiration_days']) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Security & Support</h2>
                <p class="mt-1 text-sm text-slate-400">Previously managed on the Super Admin dashboard.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Session Timeout (minutes)</label>
                        <input name="session_timeout_minutes" type="number" min="5" max="480"
                            value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes']) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Support Email</label>
                        <input name="support_email" type="email" value="{{ old('support_email', $settings['support_email']) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-300">IP Whitelist (comma-separated)</label>
                        <input name="ip_whitelist" type="text" value="{{ old('ip_whitelist', $ipWhitelistText) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="ip_whitelist_enabled" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('ip_whitelist_enabled', $settings['ip_whitelist_enabled']))>
                        <span class="font-medium text-white">Enable IP Whitelist for Admin Access</span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                        <input type="checkbox" name="two_factor_recovery_enabled" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('two_factor_recovery_enabled', $settings['two_factor_recovery_enabled']))>
                        <span class="font-medium text-white">Enable Passkey Recovery Flow</span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300 sm:col-span-2">
                        <input type="checkbox" name="public_results_published" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('public_results_published', $settings['public_results_published']))>
                        <span class="font-medium text-white">Mark public results as published (platform flag)</span>
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Support Team Label</label>
                        <input name="support_team_label" type="text" value="{{ old('support_team_label', $settings['support_team_label']) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    Save Settings
                </button>
            </div>
        </form>
    </x-admin-portal>
</x-app-layout>
