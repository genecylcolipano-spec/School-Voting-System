<x-app-layout>
    <div
        x-data="{
            sidebarOpen: false,
            collapsed: sessionStorage.getItem('studentSidebarCollapsed') === 'true',
            toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    this.collapsed = !this.collapsed;
                    sessionStorage.setItem('studentSidebarCollapsed', this.collapsed ? 'true' : 'false');
                } else {
                    this.sidebarOpen = !this.sidebarOpen;
                }
            }
        }"
        @keydown.escape.window="sidebarOpen = false"
        class="h-screen overflow-hidden bg-slate-950 text-slate-100"
    >
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 lg:hidden"
            style="display: none;"
        ></div>

        <div class="flex h-full min-h-0 overflow-hidden">
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
                        subtitle="Student Portal"
                        collapsed-aware
                        gradient="from-violet-600 to-indigo-500"
                        icon-class="text-white"
                        shadow-class="shadow-violet-900/40"
                    />
                </div>

                @include('student.partials.sidebar-nav')
            </aside>

            <div
                class="flex min-w-0 flex-1 flex-col overflow-y-auto"
                :class="collapsed ? 'lg:pl-20' : 'lg:pl-72'"
            >
                <header class="sticky top-0 z-30 border-b border-cyan-500/10 bg-slate-950/90 backdrop-blur-md">
                    <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                @click="toggleSidebar()"
                                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-cyan-500/20 bg-slate-900 text-cyan-300 hover:bg-slate-800"
                                aria-label="Toggle sidebar"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="min-w-0">
                                <p class="truncate text-sm text-slate-400">Welcome back, {{ $user->name }}</p>
                                <h1 class="truncate text-lg font-bold text-white sm:text-xl">Student Dashboard</h1>
                            </div>
                        </div>

                        <div class="relative flex items-center gap-2">
                            <x-notification-center
                                :feed-url="route('student.notifications.feed')"
                                :index-url="route('student.notifications.index')"
                                :mark-all-url="route('student.notifications.read')"
                                mark-one-url-template="{{ route('student.notifications.read-one', ['notification' => '__ID__']) }}"
                                :initial-count="$notificationsCount"
                                theme="student"
                            />

                            @include('student.partials.account-menu', ['user' => $user])
                        </div>
                    </div>
                </header>

                <main id="top" class="min-h-0 flex-1 space-y-8 px-4 py-6 sm:px-6 lg:px-8">
                    @if ($user->passkeys_count === 0)
                        <div class="max-w-xl">
                            <x-passkey-register
                                :register-options-url="route('register.passkey.options')"
                                :register-verify-url="route('register.passkey.verify')"
                            />
                        </div>
                    @endif

                    {{-- 1. Hero --}}
                    <section class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-sky-900/80 via-slate-900 to-emerald-900/30 p-6 sm:p-8">
                        <span class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-cyan-200">Student Portal</span>
                        <h2 class="mt-4 text-2xl font-bold text-white sm:text-3xl">Welcome back, {{ $firstName }}!</h2>
                        <p class="mt-3 max-w-2xl text-slate-300">
                            Participate in school elections, discover campus events, support fundraising campaigns, join talent competitions, and stay updated with school announcements.
                        </p>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                            @if ($hasActiveElection)
                                <a
                                    href="{{ $voteNowUrl }}"
                                    class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-900/30 transition hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                                    aria-label="Vote now"
                                >
                                    Vote Now
                                </a>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="inline-flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-2 rounded-xl border border-slate-700 bg-slate-800/60 px-5 py-2.5 text-sm font-semibold text-slate-400 sm:w-auto"
                                >
                                    Vote Now
                                </button>
                            @endif

                            <a
                                href="{{ route('student.events.index') }}"
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-cyan-400/40 bg-transparent px-5 py-2.5 text-sm font-semibold text-cyan-100 transition hover:border-cyan-300/60 hover:bg-cyan-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 sm:w-auto"
                                aria-label="View school events"
                            >
                                View School Events
                            </a>
                        </div>

                        @unless ($hasActiveElection)
                            <p class="mt-3 text-sm text-slate-400">No active elections at the moment.</p>
                        @endunless
                    </section>

                    {{-- 2. Today's Activity (interactive navigation) --}}
                    <section>
                        <div class="mb-4">
                            <h2 class="text-xl font-bold text-white">Student Overview</h2>
                            <p class="mt-1 text-sm text-slate-400">Current status of your activities and available opportunities.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
                            @foreach ($activityCards as $card)
                                @php
                                    $cardClasses = 'group relative flex h-full min-h-[9.5rem] flex-col rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-4 shadow-lg shadow-black/10 transition duration-300 sm:p-5 '
                                        .($card['enabled']
                                            ? 'cursor-pointer hover:-translate-y-1 hover:border-violet-400/45 hover:bg-slate-900 hover:shadow-xl hover:shadow-violet-900/25 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-950'
                                            : 'cursor-default opacity-80');
                                @endphp

                                @if ($card['enabled'])
                                    <a href="{{ $card['href'] }}" class="{{ $cardClasses }}" aria-label="{{ $card['title'] }}">
                                @else
                                    <div class="{{ $cardClasses }}" aria-disabled="true">
                                @endif
                                    <div class="mb-3 flex items-start justify-between gap-2">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/15 text-violet-300 ring-1 ring-violet-500/20 {{ $card['enabled'] ? 'transition group-hover:bg-violet-500/25' : '' }}">
                                            @switch ($card['icon'])
                                                @case('vote')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                                    @break
                                                @case('events')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    @break
                                                @case('talent')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                                    @break
                                                @case('fundraising')
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    @break
                                                @default
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                            @endswitch
                                        </span>
                                        <span class="text-2xl font-bold text-white">{{ $card['count'] }}</span>
                                    </div>

                                    <p class="text-sm font-semibold text-white">{{ $card['title'] }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $card['status'] }}</p>

                                    <p @class([
                                        'mt-auto pt-3 text-sm font-semibold',
                                        'text-cyan-300 transition group-hover:text-cyan-200' => $card['enabled'],
                                        'text-slate-500' => ! $card['enabled'],
                                    ])>
                                        @if ($card['enabled'])
                                            {{ $card['action'] }}
                                        @else
                                            Unavailable
                                        @endif
                                    </p>

                                @if ($card['enabled'])
                                    </a>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </section>

                    {{-- 3. Upcoming Activities --}}
                    @include('dashboards._upcoming-activities')

                    {{-- 5. Latest Announcements --}}
                    <section>
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-white">Latest Announcements</h2>
                                <p class="mt-1 text-sm text-slate-400">Newest updates for students and the school community.</p>
                            </div>
                            <a href="{{ route('student.announcements.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">View All</a>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse ($announcements as $item)
                                <article class="flex h-full flex-col rounded-2xl border border-cyan-500/15 bg-slate-900/70 p-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($item->category)
                                            <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $item->category->badgeClasses() }}">{{ $item->category->label() }}</span>
                                        @endif
                                        <span class="text-xs text-slate-500">{{ optional($item->published_at)->format('M d, Y') }}</span>
                                    </div>
                                    <h3 class="mt-3 font-semibold text-white">{{ $item->title }}</h3>
                                    <p class="mt-2 line-clamp-2 flex-1 text-sm text-slate-400">{{ $item->summary }}</p>
                                    <a href="{{ route('student.announcements.show', $item) }}" class="mt-4 inline-flex text-sm font-semibold text-cyan-300 hover:text-cyan-200">Read more →</a>
                                </article>
                            @empty
                                <p class="text-sm text-slate-500 md:col-span-2 xl:col-span-3">No announcements right now.</p>
                            @endforelse
                        </div>
                    </section>

                    {{-- 6. Recent Notifications --}}
                    <section>
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-white">Recent Notifications</h2>
                                <p class="mt-1 text-sm text-slate-400">Your latest portal alerts.</p>
                            </div>
                            <a href="{{ route('student.notifications.index') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">View All Notifications</a>
                        </div>
                        <div class="divide-y divide-slate-800 rounded-2xl border border-cyan-500/15 bg-slate-900/70">
                            @forelse ($notifications as $note)
                                <div class="flex gap-3 px-5 py-4">
                                    <span class="text-xl" aria-hidden="true">{{ $note['icon'] }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-medium text-slate-200">{{ $note['title'] }}</p>
                                            @if ($note['read'])
                                                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Read</span>
                                            @else
                                                <span class="rounded-full bg-cyan-500/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cyan-300">Unread</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 line-clamp-2 text-sm text-slate-400">{{ $note['message'] }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $note['time'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="px-5 py-8 text-center text-sm text-slate-500">No notifications yet.</p>
                            @endforelse
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
    @vite(['resources/js/notification-center.js'])
</x-app-layout>
