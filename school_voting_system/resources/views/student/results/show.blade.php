<x-app-layout>
    <x-student-portal title="{{ $detail['name'] }}" :user="$user" :notifications-count="$notificationsCount">
        @include('results._event-detail', [
            'detail' => $detail,
            'backUrl' => route('student.results.index'),
            'backLabel' => 'All Results',
            'accent' => 'cyan',
        ])
    </x-student-portal>
</x-app-layout>
