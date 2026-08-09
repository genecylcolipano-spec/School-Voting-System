<?php
    use Illuminate\Support\Facades\Gate;
    $talentActive = request()->routeIs('admin.talent-competition.*', 'admin.talent-participants.*', 'admin.live.talent');
    $eventsActive = request()->routeIs('admin.events-talent.*', 'admin.events.*') || $talentActive;
    $votingActive = request()->routeIs('admin.elections.*', 'admin.campaigns.*', 'admin.live.election');
    $resultsActive = request()->routeIs('admin.results.*');
    $fundraisingActive = request()->routeIs('admin.fundraisers.*');
    $commsActive = request()->routeIs('admin.announcements.*', 'admin.notifications.*');
    $reportsActive = request()->routeIs('admin.analytics.*', 'admin.reports.*')
        || (! $isSuperAdmin && request()->routeIs('admin.audit-logs.*'));
    $canViewStudents = Gate::check('viewAnyStudents');
    $studentsActive = request()->routeIs('admin.students.*');
    $userManagementActive = request()->routeIs(
        'super-admin.administrators.*',
        'super-admin.faculty.*',
        'super-admin.staff.*'
    ) || $studentsActive;
    $rosterManagementActive = request()->routeIs('super-admin.roster.*', 'super-admin.allowed-students.*');
    $systemManagementActive = request()->routeIs('super-admin.system.*');

    $subLink = fn (bool $active) => 'flex items-center gap-2 rounded-lg px-3 py-2 text-sm '.($active
        ? 'bg-violet-500/15 text-violet-200'
        : 'text-slate-400 hover:bg-slate-800/60 hover:text-white');
    $groupBtn = fn (bool $active) => 'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium transition '.($active
        ? 'text-violet-200'
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');
    $topLink = fn (bool $active) => 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '.($active
        ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white shadow-lg shadow-violet-900/30'
        : 'text-slate-400 hover:bg-slate-800/70 hover:text-white');
?>

<nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 py-4" x-data="{
    openEvents: <?php echo e($eventsActive ? 'true' : 'false'); ?>,
    openTalent: <?php echo e($talentActive ? 'true' : 'false'); ?>,
    openVoting: <?php echo e($votingActive ? 'true' : 'false'); ?>,
    openResults: <?php echo e($resultsActive ? 'true' : 'false'); ?>,
    openFundraising: <?php echo e($fundraisingActive ? 'true' : 'false'); ?>,
    openComms: <?php echo e($commsActive ? 'true' : 'false'); ?>,
    openReports: <?php echo e($reportsActive ? 'true' : 'false'); ?>,
    openStudents: <?php echo e($isSuperAdmin && $userManagementActive ? 'true' : 'false'); ?>,
    openRoster: <?php echo e($isSuperAdmin && $rosterManagementActive ? 'true' : 'false'); ?>,
    openSystem: <?php echo e($isSuperAdmin && $systemManagementActive ? 'true' : 'false'); ?>,
}">
    
    <a href="<?php echo e(route($dashboardRoute)); ?>" @click="sidebarOpen = false" class="<?php echo e($topLink($onDashboard)); ?>">
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        <span x-show="!collapsed" class="truncate">Dashboard</span>
    </a>

    <div class="my-3 border-t border-violet-500/10"></div>

    
    <div>
        <button type="button" @click="openEvents = !openEvents" class="<?php echo e($groupBtn($eventsActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Event Management</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openEvents ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openEvents && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.events-talent.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.events-talent.*'))); ?>">Events Dashboard</a>
            <a href="<?php echo e(route('admin.events.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.events.*'))); ?>">School Events</a>

            
            <div>
                <button type="button" @click="openTalent = !openTalent" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm <?php echo e($talentActive ? 'text-violet-200' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'); ?>">
                    <span class="flex-1 truncate">Talent Competitions</span>
                    <svg class="h-3.5 w-3.5 shrink-0 transition" :class="openTalent ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openTalent" x-transition class="mt-0.5 space-y-0.5 border-l border-violet-500/10 pl-3">
                    <a href="<?php echo e(route('admin.talent-competition.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.talent-competition.*'))); ?>">Competition Management</a>
                    <a href="<?php echo e(route('admin.talent-participants.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.talent-participants.*'))); ?>">Participants</a>
                    <a href="<?php echo e(route('admin.live.talent')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.live.talent'))); ?>">Live Monitoring</a>
                </div>
            </div>
        </div>
    </div>

    
    <div>
        <button type="button" @click="openVoting = !openVoting" class="<?php echo e($groupBtn($votingActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Voting Management</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openVoting ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openVoting && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.elections.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.elections.*'))); ?>">Elections</a>
            <a href="<?php echo e(route('admin.campaigns.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.campaigns.*'))); ?>">Campaigns</a>
            <a href="<?php echo e(route('admin.live.election')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.live.election'))); ?>">Live Monitoring</a>
        </div>
    </div>

    
    <div>
        <button type="button" @click="openResults = !openResults" class="<?php echo e($groupBtn($resultsActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Results</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openResults ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openResults && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.results.elections')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.results.elections') || request()->routeIs('admin.results.election.*'))); ?>">Election Results</a>
            <a href="<?php echo e(route('admin.results.competitions')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.results.competitions') || request()->routeIs('admin.results.talent.*'))); ?>">Talent Competition Results</a>
        </div>
    </div>

    
    <div>
        <button type="button" @click="openFundraising = !openFundraising" class="<?php echo e($groupBtn($fundraisingActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Fundraising</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openFundraising ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openFundraising && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.fundraisers.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.fundraisers.*') && ! request()->routeIs('admin.fundraisers.donations', 'admin.fundraisers.transactions'))); ?>">Campaigns</a>
            <a href="<?php echo e(route('admin.fundraisers.donations')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.fundraisers.donations'))); ?>">Donations</a>
            <a href="<?php echo e(route('admin.fundraisers.transactions')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.fundraisers.transactions'))); ?>">Transactions</a>
        </div>
    </div>

    
    <div>
        <button type="button" @click="openComms = !openComms" class="<?php echo e($groupBtn($commsActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Communication</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openComms ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openComms && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.announcements.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.announcements.*'))); ?>">Announcements</a>
            <a href="<?php echo e(route('admin.notifications.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.notifications.*'))); ?>">Notifications</a>
        </div>
    </div>

    
    <div>
        <button type="button" @click="openReports = !openReports" class="<?php echo e($groupBtn($reportsActive)); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span x-show="!collapsed" class="flex-1 truncate">Reports & Analytics</span>
            <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openReports ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="openReports && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
            <a href="<?php echo e(route('admin.analytics.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.analytics.*'))); ?>">Dashboard Analytics</a>
            <a href="<?php echo e(route('admin.reports.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.reports.index'))); ?>">Election Reports</a>
            <a href="<?php echo e(route('admin.reports.talent')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.reports.talent'))); ?>">Talent Competition Reports</a>
            <a href="<?php echo e(route('admin.reports.fundraising')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.reports.fundraising'))); ?>">Fundraising Reports</a>
            <?php if (! ($isSuperAdmin)): ?>
                <a href="<?php echo e(route('admin.audit-logs.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.audit-logs.*'))); ?>">Audit Log</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="my-3 border-t border-violet-500/10"></div>

    
    <?php if($isSuperAdmin): ?>
        <div>
            <button type="button" @click="openStudents = !openStudents" class="<?php echo e($groupBtn($userManagementActive)); ?>">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span x-show="!collapsed" class="flex-1 truncate">User Management</span>
                <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openStudents ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openStudents && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
                <a href="<?php echo e(route('admin.students.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('admin.students.*'))); ?>">Students</a>
                <a href="<?php echo e(route('super-admin.faculty.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.faculty.*'))); ?>">Faculty</a>
                <a href="<?php echo e(route('super-admin.administrators.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.administrators.*'))); ?>">Administrators</a>
            </div>
        </div>

        
        <div>
            <button type="button" @click="openRoster = !openRoster" class="<?php echo e($groupBtn($rosterManagementActive)); ?>">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="!collapsed" class="flex-1 truncate">Roster Management</span>
                <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openRoster ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openRoster && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
                <a href="<?php echo e(route('super-admin.roster.students.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.roster.students.*'))); ?>">Student Roster</a>
                <a href="<?php echo e(route('super-admin.roster.faculty.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.roster.faculty.*'))); ?>">Faculty Roster</a>
                <a href="<?php echo e(route('super-admin.roster.administrators.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.roster.administrators.*'))); ?>">Administrator Roster</a>
            </div>
        </div>

        
        <div>
            <button type="button" @click="openSystem = !openSystem" class="<?php echo e($groupBtn($systemManagementActive)); ?>">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                <span x-show="!collapsed" class="flex-1 truncate">System Management</span>
                <svg x-show="!collapsed" class="h-4 w-4 shrink-0 transition" :class="openSystem ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="openSystem && !collapsed" x-transition class="mt-1 space-y-0.5 pl-4">
                <a href="<?php echo e(route('super-admin.system.settings.edit')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.system.settings.*'))); ?>">System Settings</a>
                <a href="<?php echo e(route('super-admin.system.maintenance.edit')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.system.maintenance.*'))); ?>">Maintenance Mode</a>
                <a href="<?php echo e(route('super-admin.system.backups.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.system.backups.*'))); ?>">Backup & Restore</a>
                <a href="<?php echo e(route('super-admin.system.audit.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($subLink(request()->routeIs('super-admin.system.audit.*'))); ?>">Audit Logs</a>
            </div>
        </div>
    <?php elseif($canViewStudents): ?>
        <a href="<?php echo e(route('admin.students.index')); ?>" @click="sidebarOpen = false" class="<?php echo e($topLink(request()->routeIs('admin.students.*'))); ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span x-show="!collapsed" class="truncate">User Management</span>
        </a>
    <?php endif; ?>

    
    <a href="<?php echo e(route('profile.edit')); ?>" @click="sidebarOpen = false" class="<?php echo e($topLink(request()->routeIs('profile.*'))); ?>">
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span x-show="!collapsed" class="truncate">Settings</span>
    </a>

    
    <form method="POST" action="<?php echo e(route('logout')); ?>" class="pt-2">
        <?php echo csrf_field(); ?>
        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-400 transition hover:bg-slate-800/70 hover:text-rose-300">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span x-show="!collapsed">Logout</span>
        </button>
    </form>
</nav>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/sidebar-nav.blade.php ENDPATH**/ ?>