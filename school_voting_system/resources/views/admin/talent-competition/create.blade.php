<x-app-layout>
    <x-admin-portal
        :title="'Create Talent Competition'"
        :user="$user"
        :notifications-count="$notificationsCount"
        :assigned-role="$assignedRole"
    >
        @if ($hostElections->isEmpty())
            <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                Create an election first, then you can add a talent competition.
                @if ($user->isSuperAdmin() || $user->hasPermission('modify_elections'))
                    <a href="{{ route('admin.elections.create') }}" class="ml-1 font-semibold text-amber-50 underline decoration-amber-300/60 underline-offset-2 hover:text-white">Create election</a>
                @endif
            </div>
        @else
            @include('admin.talent-competition._form', [
                'talentEvent' => null,
                'election' => $election,
                'hostElections' => $hostElections,
                'canPickElection' => $canPickElection,
                'types' => $types,
                'categories' => $categories,
                'votingMethods' => $votingMethods,
                'registrationMethods' => $registrationMethods,
                'submissionMethods' => $submissionMethods,
                'rankingMethods' => $rankingMethods,
            ])
        @endif
    </x-admin-portal>

    @vite(['resources/js/regular-admin-dashboard.js', 'resources/js/event-image-preview.js', 'resources/js/talent-competition-create.js'])
</x-app-layout>
