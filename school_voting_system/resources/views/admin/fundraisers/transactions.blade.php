<x-app-layout>
    <x-admin-portal title="Transactions" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Transactions',
            'description' => 'Chronological ledger of all fundraising transactions.',
            'showAction' => false,
        ])

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.fundraisers.donations') }}" class="text-sm font-semibold text-violet-300 hover:text-violet-200">← Donations</a>
            <a href="{{ route('admin.fundraisers.index') }}" class="text-sm font-semibold text-slate-400 hover:text-white">Campaigns →</a>

            <form method="GET" action="{{ route('admin.fundraisers.transactions') }}" class="ml-auto">
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
                        <th class="px-4 py-3">Txn ID</th>
                        <th class="px-4 py-3">Date &amp; Time</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Donor</th>
                        <th class="px-4 py-3">Campaign</th>
                        <th class="px-4 py-3">Currency</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($transactions as $txn)
                        <tr class="text-slate-300">
                            <td class="px-4 py-3 font-mono text-xs text-slate-400">#{{ str_pad((string) $txn->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ optional($txn->donated_at)->format('M d, Y g:i A') }}</td>
                            <td class="px-4 py-3"><span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-200">Donation</span></td>
                            <td class="px-4 py-3">{{ $txn->is_anonymous ? 'Anonymous' : ($txn->donor?->name ?? '—') }}</td>
                            <td class="px-4 py-3">{{ $txn->fundraiser?->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs uppercase text-slate-400">{{ $txn->currency ?: 'PHP' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300">₱{{ number_format((float) $txn->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No transactions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $transactions->links() }}</div>
    </x-admin-portal>
</x-app-layout>
