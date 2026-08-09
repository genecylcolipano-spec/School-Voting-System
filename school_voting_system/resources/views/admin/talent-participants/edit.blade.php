<x-app-layout>
    <x-admin-portal title="Edit Participant" :user="$user" :notifications-count="$notificationsCount">
        @include('admin.partials.page-header', [
            'title' => 'Edit Participant',
            'description' => $entry->display_name,
            'showAction' => false,
        ])

        @include('admin.talent-participants._form', [
            'entry' => $entry,
            'categories' => $categories,
        ])
    </x-admin-portal>
</x-app-layout>
