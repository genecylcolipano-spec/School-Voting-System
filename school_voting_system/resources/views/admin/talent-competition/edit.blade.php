<x-app-layout>
    <x-admin-portal
        :title="'Edit Talent Competition'"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @include('admin.talent-competition._form', [
            'talentEvent' => $talentEvent,
            'election' => $election,
            'types' => $types,
            'categories' => $categories,
            'votingMethods' => $votingMethods,
            'registrationMethods' => $registrationMethods,
            'submissionMethods' => $submissionMethods,
            'rankingMethods' => $rankingMethods,
        ])
    </x-admin-portal>

    @vite(['resources/js/regular-admin-dashboard.js', 'resources/js/event-image-preview.js'])
</x-app-layout>
