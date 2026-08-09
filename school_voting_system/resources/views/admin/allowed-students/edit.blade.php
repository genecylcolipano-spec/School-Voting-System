<x-app-layout>
    <x-admin-portal title="Edit Roster Record" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Edit Roster Record',
            'description' => 'Update an official allowed student row.',
            'showAction' => false,
        ])

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="{{ route('super-admin.allowed-students.update', $record) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-300">Account ID</label>
                    <input name="account_id" value="{{ old('account_id', $record->account_id) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 font-mono text-white">
                    @error('account_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">First Name</label>
                        <input name="first_name" value="{{ old('first_name', $record->first_name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $record->last_name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Grade Level</label>
                        <input name="grade_level" value="{{ old('grade_level', $record->grade_level) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Section</label>
                        <input name="section" value="{{ old('section', $record->section) }}" class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
                    <a href="{{ route('super-admin.allowed-students.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                </div>
            </form>
        </section>
    </x-admin-portal>
</x-app-layout>
