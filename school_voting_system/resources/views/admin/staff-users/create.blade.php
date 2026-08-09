<x-app-layout>
    <x-admin-portal :title="$pageTitle" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $pageTitle,
            'description' => 'No self-registration. A passkey enrollment link is issued after the account is created.',
            'showAction' => false,
        ])

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="{{ $storeRoute }}" class="space-y-5">
                @csrf

                <div>
                    <label for="account_id" class="block text-sm font-medium text-slate-300">Account ID</label>
                    <input id="account_id" name="account_id" type="text" value="{{ old('account_id', $suggestedAccountId) }}" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    @error('account_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    @error('name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        placeholder="name@gmail.com"
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    <p class="mt-1 text-xs text-slate-500">Must be a real inbox that can receive mail. Addresses like <span class="font-mono">@school.local</span> will not work.</p>
                    @error('email')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                @if ($staffRoles->isNotEmpty())
                    <div>
                        <label for="staff_role_id" class="block text-sm font-medium text-slate-300">Staff Role (optional)</label>
                        <select id="staff_role_id" name="staff_role_id"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                            <option value="">— None —</option>
                            @foreach ($staffRoles as $staffRole)
                                <option value="{{ $staffRole->id }}" @selected((string) old('staff_role_id') === (string) $staffRole->id)>
                                    {{ $staffRole->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_role_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                @endif

                <label class="flex items-start gap-3 rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3 text-sm text-slate-300">
                    <input type="checkbox" name="send_enrollment_email" value="1" class="mt-1 rounded border-slate-600 bg-slate-900 text-violet-500" @checked(old('send_enrollment_email', true))>
                    <span>
                        <span class="font-medium text-white">Email passkey enrollment link</span>
                        <span class="mt-0.5 block text-slate-500">If unchecked, the link is still shown after create so you can share it manually.</span>
                    </span>
                </label>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                        Create Account
                    </button>
                    <a href="{{ $indexRoute }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        Cancel
                    </a>
                </div>
            </form>
        </section>
    </x-admin-portal>
</x-app-layout>
