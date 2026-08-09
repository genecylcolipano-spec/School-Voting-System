@props([
    'title' => 'Faculty Portal',
    'user',
    'notificationsCount' => 0,
])

@php
    $roleLabel = $user->roleLabel();
@endphp

<div
    x-data="{
        sidebarOpen: false,
        collapsed: sessionStorage.getItem('facultySidebarCollapsed') === 'true',
        toggleSidebar() {
            if (window.innerWidth >= 1024) {
                this.collapsed = !this.collapsed;
                sessionStorage.setItem('facultySidebarCollapsed', this.collapsed ? 'true' : 'false');
            } else {
                this.sidebarOpen = !this.sidebarOpen;
            }
        }
    }"
    @keydown.escape.window="sidebarOpen = false"
    class="min-h-screen bg-slate-950 text-slate-100"
>
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden"
        style="display: none;"
    ></div>

    <aside
        :class="{
            'translate-x-0': sidebarOpen,
            '-translate-x-full': !sidebarOpen,
            'lg:translate-x-0': true,
            'lg:w-20': collapsed,
            'lg:w-72': !collapsed
        }"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-teal-500/15 bg-slate-950 transition-all duration-300"
    >
        <div class="border-b border-teal-500/10 px-5 py-5">
            <x-school-brand
                subtitle="Faculty Portal"
                collapsed-aware
                gradient="from-teal-500 to-emerald-400"
                icon-class="text-slate-950"
                shadow-class="shadow-teal-900/40"
            />
        </div>

        @include('faculty.partials.sidebar-nav')
    </aside>

    <div
        class="min-w-0 bg-slate-950"
        :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
        <header class="sticky top-0 z-30 border-b border-teal-500/10 bg-slate-950/90 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-teal-500/20 bg-slate-900 text-teal-300 hover:bg-slate-800"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-sm text-slate-400">Welcome back, {{ $user->name }}</p>
                        <h1 class="truncate text-lg font-bold text-white sm:text-xl">{{ $title }}</h1>
                    </div>
                </div>

                <div class="relative flex items-center gap-2">
                    <x-notification-center
                        :feed-url="route('faculty.notifications.feed')"
                        :index-url="route('faculty.notifications.index')"
                        :mark-all-url="route('faculty.notifications.read')"
                        mark-one-url-template="{{ route('faculty.notifications.read-one', ['notification' => '__ID__']) }}"
                        :initial-count="$notificationsCount"
                        theme="faculty"
                    />

                    @include('faculty.partials.account-menu', ['user' => $user, 'roleLabel' => $roleLabel])
                </div>
            </div>
        </header>

        <main class="space-y-6 bg-slate-950 px-4 py-6 pb-8 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @vite(['resources/js/notification-center.js'])
</div>
