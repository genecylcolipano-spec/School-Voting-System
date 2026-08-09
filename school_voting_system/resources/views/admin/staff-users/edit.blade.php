<x-app-layout>
    <x-admin-portal :title="$pageTitle" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $pageTitle,
            'description' => 'Update profile details. Account ID cannot be changed.',
            'showAction' => false,
        ])

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="{{ $updateRoute }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-300">Account ID</label>
                    <input type="text" value="{{ $account->account_id }}" disabled class="mt-1 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-2.5 font-mono text-slate-400">
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $account->name) }}" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    @error('name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $account->email) }}" required
                        class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    @error('email')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>

                @if ($staffRoles->isNotEmpty())
                    <div>
                        <label for="staff_role_id" class="block text-sm font-medium text-slate-300">Staff Role (optional)</label>
                        <select id="staff_role_id" name="staff_role_id"
                            class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white focus:border-violet-500/50 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                            <option value="">— None —</option>
                            @foreach ($staffRoles as $staffRole)
                                <option value="{{ $staffRole->id }}" @selected((string) old('staff_role_id', $account->staff_role_id) === (string) $staffRole->id)>
                                    {{ $staffRole->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_role_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">Save changes</button>
                    <a href="{{ $indexRoute }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                </div>
            </form>
        </section>
    </x-admin-portal>
</x-app-layout>
