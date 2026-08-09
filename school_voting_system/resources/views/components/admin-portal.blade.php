@props([
    'title' => 'Admin Dashboard',
    'user',
    'notificationsCount' => 0,
    'assignedRole' => null,
])

@php
    use App\Support\AdminPortal;
    $dashboardRoute = AdminPortal::dashboardRouteName($user);
    $onDashboard = request()->routeIs('admin.dashboard', 'super-admin.dashboard');
    $isSuperAdmin = $user->isSuperAdmin();
    $portalLabel = $isSuperAdmin ? 'Super Admin Portal' : 'Admin Portal';
    $roleLabel = $user->roleLabel();
    $welcomeLabel = $user->name;
@endphp

{{--
    Single-page scroll shell:
    - Document/body scrolls naturally (one primary scrollbar).
    - Sidebar is fixed; nav scrolls independently only when menu overflows.
    - Main content is not a nested scroll container.
--}}
<div
    x-data="{
        sidebarOpen: false,
        collapsed: sessionStorage.getItem('adminSidebarCollapsed') === 'true',
        toggleSidebar() {
            if (window.innerWidth >= 1024) {
                this.collapsed = !this.collapsed;
                sessionStorage.setItem('adminSidebarCollapsed', this.collapsed ? 'true' : 'false');
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
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-violet-500/15 bg-slate-950 transition-all duration-300"
    >
        <div class="border-b border-violet-500/10 px-5 py-5">
            <x-school-brand
                :subtitle="$portalLabel"
                collapsed-aware
                gradient="from-violet-600 to-indigo-500"
                icon-class="text-white"
                shadow-class="shadow-violet-900/40"
            />
        </div>

        @include('admin.partials.sidebar-nav')
    </aside>

    <div
        class="min-w-0 bg-slate-950"
        :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
    >
        <header class="sticky top-0 z-30 border-b border-violet-500/10 bg-slate-950/90 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button
                        type="button"
                        @click="toggleSidebar()"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-violet-500/20 bg-slate-900 text-violet-300 hover:bg-slate-800"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <p class="truncate text-sm text-slate-400">Welcome back, {{ $welcomeLabel }}</p>
                        <h1 class="truncate text-lg font-bold text-white sm:text-xl">{{ $title }}</h1>
                    </div>
                </div>

                <div class="relative flex max-w-xl flex-1 items-center gap-2">
                    @if ($isSuperAdmin)
                        <div class="relative hidden w-full sm:block">
                            <input id="super-admin-search" type="search" placeholder="Search accounts, students, elections…"
                                class="w-full rounded-xl border border-violet-500/20 bg-slate-900/80 px-4 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-400/50 focus:outline-none">
                            <div id="super-admin-search-results" class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-violet-500/20 bg-slate-900 shadow-xl"></div>
                        </div>
                    @endif
                </div>

                <div class="relative flex items-center gap-2">
                    <x-notification-center
                        :feed-url="route('admin.notifications.feed')"
                        :index-url="route('admin.notifications.index')"
                        :mark-all-url="route('admin.notifications.read')"
                        mark-one-url-template="{{ route('admin.notifications.read-one', ['notification' => '__ID__']) }}"
                        :initial-count="$notificationsCount"
                        theme="admin"
                    />

                    @if ($isSuperAdmin)
                        <a href="{{ route('admin.recovery.index') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-violet-500/20 bg-slate-900 text-violet-300 hover:bg-slate-800" title="Passkey recovery queue">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </a>
                    @endif

                    <x-responsive-popover
                        align="end"
                        mobile-title="Account"
                        width-class="w-72"
                        panel-class="border-violet-500/20"
                    >
                        <x-slot:trigger>
                            <button
                                type="button"
                                class="inline-flex max-w-[14rem] items-center gap-2 rounded-xl border border-violet-500/20 bg-slate-900/80 py-1.5 pl-1.5 pr-2.5 text-sm font-semibold text-white hover:bg-slate-800 sm:max-w-xs sm:pr-3"
                                aria-haspopup="menu"
                            >
                                <x-user-avatar :user="$user" size="nav" class="!h-10 !w-10 sm:!h-11 sm:!w-11" />
                                <span class="hidden truncate sm:inline">{{ $user->name }}</span>
                                <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </x-slot:trigger>

                        <div class="flex items-center gap-3 border-b border-slate-800 px-4 py-4">
                            <x-user-avatar :user="$user" size="lg" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                                <p class="mt-0.5 text-xs font-medium text-violet-300">{{ $roleLabel }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-400">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="py-1">
                            <a
                                href="{{ route('profile.edit', ['section' => 'profile']) }}"
                                data-popover-close
                                role="menuitem"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                            >
                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </a>
                            <a
                                href="{{ route('profile.edit') }}"
                                data-popover-close
                                role="menuitem"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                            >
                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Settings
                            </a>
                            @if ($isSuperAdmin)
                                <a
                                    href="{{ route('admin.reports.index') }}"
                                    data-popover-close
                                    role="menuitem"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-violet-300"
                                >
                                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    System Reports
                                </a>
                            @endif
                        </div>

                        <div class="border-t border-slate-800 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    role="menuitem"
                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-rose-300 hover:bg-slate-800"
                                >
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </x-responsive-popover>
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

    @include('admin.partials.confirm-modal')
    @vite(['resources/js/notification-center.js', 'resources/js/admin-confirm.js'])
</div>
