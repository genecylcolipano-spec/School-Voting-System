<x-app-layout>
    <x-faculty-portal title="Notifications" :user="$user" :notifications-count="$notificationsCount">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Notification Center</h1>
                <p class="mt-1 text-sm text-slate-400">Updates and reminders for your faculty account.</p>
            </div>
            <a href="{{ route('faculty.dashboard') }}" class="rounded-xl border border-teal-500/25 bg-slate-900 px-4 py-2 text-sm font-semibold text-teal-300 hover:bg-slate-800">Back to dashboard</a>
        </div>

        <x-notifications-index
            :notifications="$notifications"
            :filters="$filters"
            :index-route="route('faculty.notifications.index')"
            :mark-all-route="$markAllRoute"
            :mark-one-route-name="$markOneRouteName"
            :delete-route-name="$deleteRouteName"
            theme="faculty"
        />
    </x-faculty-portal>
</x-app-layout>
