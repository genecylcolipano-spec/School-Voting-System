@php
    $roleLabel = $roleLabel ?? $user->roleLabel();
@endphp

<x-responsive-popover
    align="end"
    mobile-title="Account"
    width-class="w-72"
    panel-class="border-teal-500/20"
>
    <x-slot:trigger>
        <button
            type="button"
            class="inline-flex max-w-[14rem] items-center gap-2 rounded-xl border border-teal-500/20 bg-slate-900/80 py-1.5 pl-1.5 pr-2.5 text-sm font-semibold text-white hover:bg-slate-800 sm:max-w-xs sm:pr-3"
            aria-haspopup="menu"
        >
            <x-user-avatar :user="$user" size="nav" theme="student" class="!h-10 !w-10 sm:!h-11 sm:!w-11" />
            <span class="hidden truncate sm:inline">{{ $user->name }}</span>
            <svg class="h-4 w-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
    </x-slot:trigger>

    <div class="flex items-center gap-3 border-b border-slate-800 px-4 py-4">
        <x-user-avatar :user="$user" size="lg" theme="student" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
            <p class="mt-0.5 text-xs font-medium text-teal-300">{{ $roleLabel }}</p>
            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $user->email }}</p>
        </div>
    </div>

    <div class="py-1">
        <a
            href="{{ route('profile.edit', ['section' => 'profile']) }}"
            data-popover-close
            role="menuitem"
            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-teal-300"
        >
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            My Profile
        </a>
        <a
            href="{{ route('profile.edit') }}"
            data-popover-close
            role="menuitem"
            class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-200 hover:bg-slate-800 hover:text-teal-300"
        >
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Settings
        </a>
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
