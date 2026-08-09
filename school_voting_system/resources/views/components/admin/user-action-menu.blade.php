@props([
    'account',
    'variant' => 'admin', // admin | faculty | student
])

@php
    $isFaculty = $variant === 'faculty';
    $isStudent = $variant === 'student';
    $isAdmin = $variant === 'admin';

    $showRoute = $isStudent
        ? route('admin.students.show', $account)
        : ($isFaculty ? route('super-admin.faculty.show', $account) : route('super-admin.administrators.show', $account));

    $editRoute = $isStudent
        ? route('admin.students.edit', $account)
        : ($isFaculty ? route('super-admin.faculty.edit', $account) : route('super-admin.administrators.edit', $account));

    $toggleRoute = $isStudent
        ? route('admin.students.toggle-active', $account)
        : route('super-admin.staff.toggle-active', $account);

    $resetRoute = $isStudent
        ? route('admin.passkey.reset', $account)
        : route('super-admin.staff.enrollment', $account);

    $archiveRoute = $isStudent
        ? route('admin.students.archive', $account)
        : route('super-admin.staff.archive', $account);

    $restoreRoute = $isStudent
        ? route('admin.students.restore', $account)
        : route('super-admin.staff.restore', $account);

    $removeRoute = route('super-admin.staff.destroy', $account);

    $removeLabel = $isFaculty ? 'Remove Faculty' : 'Remove Administrator';
    $removeConfirm = $isFaculty
        ? 'Remove Faculty?\n\nThis action cannot be undone if the account has no active assignments. Accounts with active judging or unpublished scores cannot be removed — deactivate instead.'
        : 'Remove Administrator?\n\nThis action cannot be undone if the account has no active assignments. Accounts tied to active elections, competitions, or fundraising cannot be removed — deactivate instead.';

    $itemClass = 'flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-sm text-slate-200 transition hover:bg-slate-800/90 hover:text-white';
    $dangerClass = 'flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-sm text-rose-300 transition hover:bg-rose-500/10 hover:text-rose-200';
    $sepClass = 'my-1 border-t border-slate-800';
@endphp

<x-responsive-popover
    align="end"
    mobile-title="Actions"
    width-class="w-64"
    panel-class="border-violet-500/25"
    class="justify-end"
>
    <x-slot:trigger>
        <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-500/50"
            aria-label="Open actions menu"
            aria-haspopup="menu"
        >
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10 6a1.5 1.5 0 110-3 1.5 1.5 0 010 3zm0 5.5a1.5 1.5 0 110-3 1.5 1.5 0 010 3zM11.5 16a1.5 1.5 0 10-3 0 1.5 1.5 0 003 0z"/>
            </svg>
        </button>
    </x-slot:trigger>

    <div class="max-h-[min(70vh,28rem)] overflow-y-auto py-1.5" role="none">
        @if (! $isStudent || auth()->user()?->can('updateStudentRecord', $account))
            <a href="{{ $showRoute }}" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">👁</span>
                <span>View Profile</span>
            </a>
            <a href="{{ $editRoute }}" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">✏</span>
                <span>Edit Information</span>
            </a>
        @endif

        @if ($isFaculty)
            <a href="{{ $showRoute }}#competitions" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">🏆</span>
                <span>Assign Competitions</span>
            </a>
            <a href="{{ $showRoute }}#competitions" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">📋</span>
                <span>View Assigned Competitions</span>
            </a>
        @endif

        @if (! $isStudent || auth()->user()?->can('updateStudentRecord', $account))
            <a href="{{ $showRoute }}#devices" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">📱</span>
                <span>Registered Devices</span>
            </a>
        @endif

        @if (($isStudent && auth()->user()?->can('issuePasskeyReset', $account)) || ! $isStudent)
            <form method="POST" action="{{ $resetRoute }}" onsubmit="return confirm('Generate a passkey reset / enrollment link for {{ addslashes($account->name) }}?');">
                @csrf
                <button type="submit" data-popover-close role="menuitem" class="{{ $itemClass }}">
                    <span class="w-5 shrink-0 text-center" aria-hidden="true">🔑</span>
                    <span>Reset Passkey</span>
                </button>
            </form>
        @endif

        @if (! $isStudent || auth()->user()?->can('updateStudentRecord', $account))
            <a href="{{ $showRoute }}#login-history" data-popover-close role="menuitem" class="{{ $itemClass }}">
                <span class="w-5 shrink-0 text-center" aria-hidden="true">🕒</span>
                <span>Login History</span>
            </a>
        @endif

        <div class="{{ $sepClass }}" role="separator"></div>

        @if (! $isStudent || auth()->user()?->can('updateStudentRecord', $account))
            @if ($account->archived_at)
                <form method="POST" action="{{ $restoreRoute }}" onsubmit="return confirm('Restore {{ addslashes($account->name) }}? The account will be reactivated.');">
                    @csrf
                    <button type="submit" data-popover-close role="menuitem" class="{{ $itemClass }}">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🟢</span>
                        <span>Restore Account</span>
                    </button>
                </form>
            @elseif ($account->is_active)
                <form method="POST" action="{{ $toggleRoute }}" onsubmit="return confirm('Deactivate {{ addslashes($account->name) }}?\n\nThey will not be able to sign in until reactivated.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" data-popover-close role="menuitem" class="{{ $itemClass }}">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🚫</span>
                        <span>Deactivate Account</span>
                    </button>
                </form>
            @else
                <form method="POST" action="{{ $toggleRoute }}" onsubmit="return confirm('Activate {{ addslashes($account->name) }}?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" data-popover-close role="menuitem" class="{{ $itemClass }}">
                        <span class="w-5 shrink-0 text-center" aria-hidden="true">🟢</span>
                        <span>Activate Account</span>
                    </button>
                </form>
            @endif
        @endif

        <div class="{{ $sepClass }}" role="separator"></div>

        @if ($isStudent)
            @can('updateStudentRecord', $account)
                @unless ($account->archived_at)
                    <form method="POST" action="{{ $archiveRoute }}" onsubmit="return confirm('Archive Student?\n\nVotes, submissions, donations, and login history are preserved. Students are never permanently deleted.');">
                        @csrf
                        <button type="submit" data-popover-close role="menuitem" class="{{ $dangerClass }}">
                            <span class="w-5 shrink-0 text-center" aria-hidden="true">📦</span>
                            <span>Archive Student</span>
                        </button>
                    </form>
                @endunless
            @endcan
        @else
            <form method="POST" action="{{ $removeRoute }}" onsubmit="return confirm(@js($removeConfirm));">
                @csrf
                @method('DELETE')
                <button type="submit" data-popover-close role="menuitem" class="{{ $dangerClass }}">
                    <span class="w-5 shrink-0 text-center" aria-hidden="true">🗑</span>
                    <span>{{ $removeLabel }}</span>
                </button>
            </form>
        @endif
    </div>
</x-responsive-popover>
