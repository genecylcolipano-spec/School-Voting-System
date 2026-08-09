<x-app-layout>
    <x-admin-portal title="Fundraisers" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Fundraisers',
            'action' => route('admin.fundraisers.create'),
            'actionLabel' => 'Create fundraiser',
            'showAction' => auth()->user()->can('create', App\Models\Fundraiser::class),
        ])

        <div class="overflow-x-auto rounded-2xl border border-violet-500/15 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Raised</th>
                        <th class="px-4 py-3 font-medium">Goal</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fundraisers as $fundraiser)
                        @php
                            $donationCount = (int) ($fundraiser->donations_count ?? $fundraiser->donations()->count());
                            $deleteWarning = $donationCount > 0
                                ? 'This fundraising campaign contains related data: donations, transactions.'
                                : null;
                        @endphp
                        <tr class="border-b border-slate-800/80 text-slate-200">
                            <td class="px-4 py-3">{{ $fundraiser->title }}</td>
                            <td class="px-4 py-3">₱{{ number_format((float) $fundraiser->amount_raised, 2) }}</td>
                            <td class="px-4 py-3">₱{{ number_format((float) $fundraiser->goal_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ $fundraiser->displayStatusLabel() }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @can('update', $fundraiser)
                                    <a href="{{ route('admin.fundraisers.edit', $fundraiser) }}" class="text-violet-300 hover:text-violet-200">Manage</a>
                                @endcan
                                @can('delete', $fundraiser)
                                    <x-admin.delete-action
                                        :action="route('admin.fundraisers.destroy', $fundraiser)"
                                        :warning="$deleteWarning"
                                    />
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-slate-400">No fundraisers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $fundraisers->links() }}</div>
    </x-admin-portal>
</x-app-layout>
