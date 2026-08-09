<x-app-layout>
    <x-admin-portal title="Maintenance Mode" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Maintenance Mode',
            'description' => 'Take the platform offline for scheduled maintenance while optionally allowing Super Admin access.',
            'showAction' => false,
        ])

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Current Status</p>
                <p class="mt-2 text-xl font-bold {{ $status['enabled'] ? 'text-amber-300' : 'text-emerald-300' }}">
                    {{ $status['enabled'] ? 'Maintenance' : 'Online' }}
                </p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Updated</p>
                <p class="mt-2 text-sm font-semibold text-white">{{ $status['updated_at']?->timezone(config('app.timezone'))->format('M d, Y g:i A') ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Updated By</p>
                <p class="mt-2 text-sm font-semibold text-white">{{ $status['updated_by']?->name ?? '—' }}</p>
            </div>
        </div>

        <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="{{ $status['enabled'] ? route('super-admin.system.maintenance.update') : route('super-admin.system.maintenance.enable') }}" class="space-y-5">
                @csrf
                @if ($status['enabled'])
                    @method('PUT')
                @endif

                <div>
                    <p class="text-sm font-medium text-slate-300">Mode</p>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-300">
                        <span class="inline-flex items-center gap-2 {{ ! $status['enabled'] ? 'text-emerald-300' : '' }}">
                            <span class="h-2.5 w-2.5 rounded-full {{ ! $status['enabled'] ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            Online
                        </span>
                        <span class="inline-flex items-center gap-2 {{ $status['enabled'] ? 'text-amber-300' : '' }}">
                            <span class="h-2.5 w-2.5 rounded-full {{ $status['enabled'] ? 'bg-amber-400' : 'bg-slate-600' }}"></span>
                            Maintenance
                        </span>
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-slate-300">Maintenance Message</label>
                    <textarea id="message" name="message" rows="3" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none">{{ old('message', $status['message']) }}</textarea>
                    @error('message')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="return_at" class="block text-sm font-medium text-slate-300">Estimated Return Date</label>
                        <input id="return_at" name="return_at" type="datetime-local"
                            value="{{ old('return_at', optional($status['return_at'])->format('Y-m-d\\TH:i')) }}"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        @error('return_at')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <p class="block text-sm font-medium text-slate-300">Allow Super Administrator Access</p>
                        <label class="mt-3 flex items-center gap-3 text-sm text-slate-300">
                            <input type="checkbox" name="allow_super_admin" value="1" class="rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('allow_super_admin', $status['allow_super_admin']))>
                            Yes — Super Admin may bypass maintenance
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    @if ($status['enabled'])
                        <button type="submit" class="rounded-xl border border-violet-500/30 px-5 py-2.5 text-sm font-semibold text-violet-200 hover:bg-violet-500/10">
                            Save Message
                        </button>
                    @else
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90"
                            onclick="return confirm('Enable maintenance mode? Students, Administrators, and Faculty will be blocked.')">
                            Enable Maintenance
                        </button>
                    @endif
                </div>
            </form>

            @if ($status['enabled'])
                <form method="POST" action="{{ route('super-admin.system.maintenance.disable') }}" class="mt-4 border-t border-slate-800 pt-4"
                    onsubmit="return confirm('Disable maintenance mode and bring the system online?')">
                    @csrf
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:opacity-90">
                        Disable Maintenance
                    </button>
                </form>
            @endif
        </section>
    </x-admin-portal>
</x-app-layout>
