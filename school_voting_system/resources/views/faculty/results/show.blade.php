<x-app-layout>
    <x-faculty-portal title="{{ $detail['name'] }}" :user="$user" :notifications-count="$notificationsCount">
        @include('results._event-detail', [
            'detail' => $detail,
            'backUrl' => $backUrl,
            'backLabel' => $backLabel,
            'accent' => 'teal',
        ])
    </x-faculty-portal>
</x-app-layout>
