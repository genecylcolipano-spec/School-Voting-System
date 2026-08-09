<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Election Campaigns</h1>
                    <p class="mt-1 text-sm text-slate-400">Explore published partylist campaigns and official posters.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">
                    Back to dashboard
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($campaigns as $campaign)
                    @include('student.campaigns._card', ['campaign' => $campaign])
                @empty
                    <div class="rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-6 text-slate-400 md:col-span-2">
                        No published campaigns yet. They appear here when your election admin publishes a campaign.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
