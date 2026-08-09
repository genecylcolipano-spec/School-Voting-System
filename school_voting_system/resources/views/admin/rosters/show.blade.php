<x-app-layout>
    <x-admin-portal :title="'View ' . $rosterLabel . ' Roster'" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => $rosterLabel.' Roster Record',
            'description' => $record->first_name.' '.$record->last_name,
            'showAction' => false,
        ])

        <div class="mb-4 flex flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">&larr; Back to roster</a>
            <a href="{{ route($routePrefix.'.edit', $record) }}" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Edit</a>
        </div>

        <section class="mx-auto max-w-2xl rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                    <dt class="text-slate-500">{{ $rosterIdLabel }}</dt>
                    <dd class="font-mono text-slate-200">{{ $record->account_id }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                    <dt class="text-slate-500">Name</dt>
                    <dd class="text-slate-200">{{ $record->first_name }} {{ $record->last_name }}</dd>
                </div>
                @foreach ($extraFields as $field)
                    <div class="flex justify-between gap-3 border-b border-slate-800 pb-2">
                        <dt class="text-slate-500">{{ $field['label'] }}</dt>
                        <dd class="text-slate-200">{{ $record->{$field['name']} ?: '—' }}</dd>
                    </div>
                @endforeach
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Registration Status</dt>
                    <dd>
                        @if ($record->archived_at)
                            <span class="rounded-full border border-slate-600 bg-slate-800/80 px-2 py-0.5 text-xs font-semibold text-slate-300">Archived</span>
                        @elseif ($record->is_registered)
                            <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">Registered</span>
                        @else
                            <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2 py-0.5 text-xs font-semibold text-amber-200">Not Registered</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </section>
    </x-admin-portal>
</x-app-layout>
