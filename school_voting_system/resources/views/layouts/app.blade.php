@php
    $authUser = auth()->user();
    $portalLayout = request()->routeIs([
        'student.*',
        'faculty.*',
        'admin.*',
        'super-admin.*',
        'preview.dashboard',
    ]) || (
        request()->routeIs('profile.*')
        && $authUser
        && (
            $authUser->isStudent()
            || $authUser->isFaculty()
            || $authUser->isAdmin()
            || $authUser->isSuperAdmin()
        )
    );
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['min-h-full bg-slate-950' => $portalLayout])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Support\SchoolBranding::systemName() }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body @class([
        'font-sans antialiased',
        'min-h-full bg-slate-950 text-slate-100' => $portalLayout,
    ])>
        @if ($portalLayout)
            {{-- Portal shells (admin/student) own the single sticky topbar. --}}
            {{ $slot }}
        @else
            <div class="min-h-screen bg-gray-100">
                @if (!request()->routeIs('dashboard'))
                    @include('layouts.navigation')
                @endif

                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        @endif
        @stack('scripts')
    </body>
</html>
