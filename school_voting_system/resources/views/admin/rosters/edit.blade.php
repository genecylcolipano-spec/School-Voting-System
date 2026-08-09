<x-app-layout>
    <x-admin-portal :title="'Edit ' . $rosterLabel . ' Roster'" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Edit '.$rosterLabel.' Roster Record',
            'description' => 'Update an official institutional roster row.',
            'showAction' => false,
        ])

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <form method="POST" action="{{ route($routePrefix.'.update', $record) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-300">{{ $rosterIdLabel }}</label>
                    <input name="account_id" value="{{ old('account_id', $record->account_id) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 font-mono text-white">
                    @error('account_id')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">First Name</label>
                        <input name="first_name" value="{{ old('first_name', $record->first_name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        @error('first_name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Last Name</label>
                        <input name="last_name" value="{{ old('last_name', $record->last_name) }}" required class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                        @error('last_name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                </div>
                @if (count($extraFields) > 0)
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($extraFields as $field)
                            <div>
                                <label class="block text-sm font-medium text-slate-300">{{ $field['label'] }}</label>
                                <input name="{{ $field['name'] }}" value="{{ old($field['name'], $record->{$field['name']}) }}" @required($field['required'] ?? false)
                                    class="mt-1 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-white">
                                @error($field['name'])<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white">Save</button>
                    <a href="{{ route($routePrefix.'.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-300 hover:bg-slate-800">Cancel</a>
                </div>
            </form>
        </section>
    </x-admin-portal>
</x-app-layout>
