<x-app-layout>
    <x-admin-portal title="Notifications" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Notification Center',
            'description' => 'Election events, voting updates, and platform alerts.',
        ])

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
        @endif

        <x-notifications-index
            :notifications="$notifications"
            :filters="$filters"
            :index-route="route('admin.notifications.index')"
            :mark-all-route="$markAllRoute"
            :mark-one-route-name="$markOneRouteName"
            :delete-route-name="$deleteRouteName"
            theme="admin"
        />
    </x-admin-portal>

    @vite(['resources/js/notification-center.js'])
</x-app-layout>
