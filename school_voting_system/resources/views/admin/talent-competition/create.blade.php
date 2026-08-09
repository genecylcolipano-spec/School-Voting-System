<x-app-layout>
    <x-admin-portal
        :title="'Create Talent Competition'"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @if (! $election)
            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                You need an assigned election before creating a talent competition. Contact Super Admin.
            </div>
        @else
            @include('admin.talent-competition._form', [
                'talentEvent' => null,
                'election' => $election,
                'types' => $types,
                'categories' => $categories,
                'votingMethods' => $votingMethods,
                'registrationMethods' => $registrationMethods,
                'submissionMethods' => $submissionMethods,
                'rankingMethods' => $rankingMethods,
            ])
        @endif
    </x-admin-portal>

    @vite(['resources/js/regular-admin-dashboard.js', 'resources/js/event-image-preview.js'])
</x-app-layout>
