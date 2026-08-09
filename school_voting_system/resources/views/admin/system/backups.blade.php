<x-app-layout>
    <x-admin-portal title="Backup & Restore" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Backup & Restore',
            'description' => 'Create disaster-recovery points before major operations. Restore is a future enhancement.',
            'showAction' => false,
        ])

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total Backups</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Latest Backup</p>
                <p class="mt-2 text-sm font-semibold text-white">{{ optional($stats['latest']?->completed_at)->format('M d, Y g:i A') ?? 'None yet' }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Storage Used</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ $storageUsed }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Last Backup Date</p>
                <p class="mt-2 text-sm font-semibold text-white">{{ optional($stats['last_backup_at'])->format('M d, Y') ?? '—' }}</p>
            </div>
        </div>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">Create Backup</h2>
                    <p class="mt-1 max-w-2xl text-sm text-slate-400">
                        Creates a full recovery point including users, passkeys, elections, votes, talent competitions,
                        fundraising, announcements, roster data, audit logs, system settings, and uploaded media
                        (logos, photos, attachments). Manual only — nothing is scheduled automatically.
                    </p>
                </div>
                <form method="POST" action="{{ route('super-admin.system.backups.store') }}"
                    onsubmit="return confirm('This will create a recovery point of the current system. Continue?')"
                    class="shrink-0">
                    @csrf
                    <input type="hidden" name="type" value="full_system">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Create Backup
                    </button>
                </form>
            </div>
        </section>

        <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4 sm:grid-cols-2 lg:grid-cols-6">
            <input name="search" type="search" value="{{ $filters['search'] }}" placeholder="Search name, type, creator"
                class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white lg:col-span-2">
            <select name="type" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All types</option>
                @foreach ($backupTypes as $value => $label)
                    <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
                <option value="">All statuses</option>
                <option value="completed" @selected($filters['status'] === 'completed')>Completed</option>
                <option value="failed" @selected($filters['status'] === 'failed')>Failed</option>
            </select>
            <input name="from" type="date" value="{{ $filters['from'] }}" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <input name="to" type="date" value="{{ $filters['to'] }}" class="rounded-xl border border-slate-700 bg-slate-950/50 px-3 py-2 text-sm text-white">
            <div class="flex gap-2 lg:col-span-6">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                <a href="{{ route('super-admin.system.backups.index') }}" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800">Reset</a>
            </div>
        </form>

        <section class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Backup Name</th>
                        <th class="px-4 py-3 font-medium">Created By</th>
                        <th class="px-4 py-3 font-medium">Created Date</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3">
                                <div class="font-medium text-white">{{ $backup->label }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $backup->typeLabel() }}
                                    @if ($backup->includedTableCount())
                                        · {{ $backup->includedTableCount() }} tables
                                    @endif
                                    @if ($backup->includedFileCount())
                                        · {{ $backup->includedFileCount() }} files
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $backup->creator?->name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ optional($backup->completed_at)->format('M d, Y g:i A') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $backup->formattedSize() }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-200">{{ ucfirst($backup->status) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('super-admin.system.backups.download', $backup) }}"
                                        class="rounded-lg border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-500/10">Download</a>
                                    <a href="{{ route('super-admin.system.backups.show', $backup) }}"
                                        class="rounded-lg border border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:bg-slate-800">View Details</a>
                                    <button type="button" disabled title="Available in a future update"
                                        class="cursor-not-allowed rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-500">
                                        Restore
                                    </button>
                                    <form method="POST" action="{{ route('super-admin.system.backups.destroy', $backup) }}"
                                        onsubmit="return confirm('Deleting this backup cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                No backups yet. Create a recovery point before major system changes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="mt-6">{{ $backups->links() }}</div>
    </x-admin-portal>
</x-app-layout>
