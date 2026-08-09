<x-app-layout>
    <x-admin-portal title="Add Participant" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Add Participant',
            'description' => 'Register a contestant for a talent competition.',
            'showAction' => false,
        ])

        @include('admin.talent-participants._form', [
            'entry' => null,
            'events' => $events,
            'categories' => $categories,
            'preselectedEvent' => $preselectedEvent,
        ])
    </x-admin-portal>
</x-app-layout>
