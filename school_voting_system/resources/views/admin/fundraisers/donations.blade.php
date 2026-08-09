<x-app-layout>
    <x-admin-portal title="Donations" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Donations',
            'description' => 'All contributions received across your fundraising campaigns.',
            'showAction' => false,
        ])

        {{-- Summary tiles --}}
        <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Total Raised</p>
                <p class="mt-1 text-2xl font-bold text-white">₱{{ number_format($summary['total_raised'], 2) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Donations</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['total_donations']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Unique Donors</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['unique_donors']) }}</p>
            </div>
            <div class="rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4">
                <p class="text-[10px] uppercase tracking-wide text-slate-500">Active Campaigns</p>
                <p class="mt-1 text-2xl font-bold text-white">{{ number_format($summary['active_fundraisers']) }}</p>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.fundraisers.index') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Campaigns</a>
            <a href="{{ route('admin.fundraisers.transactions') }}" class="text-sm font-semibold text-slate-400 hover:text-white">Transactions →</a>

            <form method="GET" action="{{ route('admin.fundraisers.donations') }}" class="ml-auto">
                <select name="fundraiser" onchange="this.form.submit()" class="rounded-xl border border-slate-700 bg-slate-950/60 px-4 py-2 text-sm text-white focus:border-violet-500 focus:outline-none">
                    <option value="">All campaigns</option>
                    @foreach ($fundraisers as $f)
                        <option value="{{ $f->id }}" @selected((string) $selectedFundraiser === (string) $f->id)>{{ $f->title }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-slate-800 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Donor</th>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Message</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($donations as $donation)
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-semibold text-white">
                                {{ $donation->is_anonymous ? 'Anonymous' : ($donation->donor?->name ?? '—') }}
                            </td>
                            <td class="px-4 py-3">{{ $donation->fundraiser?->title ?? '—' }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-slate-400">{{ $donation->message ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ optional($donation->donated_at)->format('M d, Y g:i A') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300">₱{{ number_format((float) $donation->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No donations recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $donations->links() }}</div>
    </x-admin-portal>
</x-app-layout>
