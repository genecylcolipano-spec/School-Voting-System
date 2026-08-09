<x-app-layout>
    <x-admin-portal title="Backup Details" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Backup Details',
            'description' => 'Recovery point contents and metadata.',
            'showAction' => false,
        ])

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('super-admin.system.backups.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Back to Backup Manager</a>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('super-admin.system.backups.download', $backup) }}"
                    class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-500/10">Download</a>
                <button type="button" disabled title="Available in a future update"
                    class="cursor-not-allowed rounded-xl border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-500">
                    Restore — Future Enhancement
                </button>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</p>
                <p class="mt-2 text-lg font-bold text-emerald-300">{{ ucfirst($details['status']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Archive Size</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $details['file_size_label'] }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Database Size</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $details['database_size_label'] }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Uploaded Files</p>
                <p class="mt-2 text-lg font-bold text-white">{{ $details['files_size_label'] }}</p>
            </div>
        </div>

        <section class="mb-6 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-white">Overview</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Backup Name</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $details['label'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Type</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $details['type_label'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Created By</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ $details['created_by'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-slate-500">Creation Date</dt>
                    <dd class="mt-1 text-sm font-medium text-white">{{ optional($details['created_at'])->format('M d, Y g:i A') ?? '—' }}</dd>
                </div>
                @if ($details['notes'])
                    <div class="sm:col-span-2">
                        <dt class="text-xs uppercase tracking-wide text-slate-500">Notes</dt>
                        <dd class="mt-1 text-sm text-slate-300">{{ $details['notes'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Included Tables</h2>
                <p class="mt-1 text-sm text-slate-400">{{ count($details['tables']) }} table(s) exported</p>
                <div class="mt-4 max-h-80 overflow-y-auto">
                    @forelse ($details['tables'] as $table)
                        <div class="flex items-center justify-between border-b border-slate-800/80 py-2 text-sm">
                            <span class="font-mono text-slate-200">{{ $table['name'] ?? $table }}</span>
                            <span class="text-slate-400">{{ number_format($table['rows'] ?? 0) }} rows</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">No table inventory stored for this backup (legacy partial export).</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-white">Included Uploaded Files</h2>
                <p class="mt-1 text-sm text-slate-400">{{ count($details['files']) }} file(s) packaged</p>
                <div class="mt-4 max-h-80 overflow-y-auto">
                    @forelse ($details['files'] as $file)
                        <div class="flex items-center justify-between gap-3 border-b border-slate-800/80 py-2 text-sm">
                            <span class="min-w-0 truncate font-mono text-slate-200" title="{{ $file['path'] ?? '' }}">{{ $file['path'] ?? '—' }}</span>
                            <span class="shrink-0 text-slate-400">
                                @php
                                    $bytes = (int) ($file['size'] ?? 0);
                                    $sizeLabel = $bytes >= 1048576
                                        ? round($bytes / 1048576, 2).' MB'
                                        : ($bytes >= 1024 ? round($bytes / 1024, 1).' KB' : $bytes.' B');
                                @endphp
                                {{ $sizeLabel }}
                            </span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-slate-500">No uploaded files were included in this backup.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </x-admin-portal>
</x-app-layout>
