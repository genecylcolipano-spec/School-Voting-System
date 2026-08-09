<x-app-layout>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white">Notification Center</h1>
                    <p class="mt-1 text-sm text-slate-400">Voting updates, results, announcements, and reminders.</p>
                </div>
                <a href="{{ route('student.dashboard') }}" class="rounded-xl border border-cyan-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-slate-800">Back to dashboard</a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
            @endif

            <x-notifications-index
                :notifications="$notifications"
                :filters="$filters"
                :index-route="route('student.notifications.index')"
                :mark-all-route="$markAllRoute"
                :mark-one-route-name="$markOneRouteName"
                :delete-route-name="$deleteRouteName"
                theme="student"
            />
        </div>
    </div>
    @vite(['resources/js/notification-center.js'])
</x-app-layout>
