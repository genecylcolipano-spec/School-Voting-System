@php
    $active = $active ?? 'election';
@endphp

<div class="mb-5 flex flex-wrap items-center gap-2">
    <a href="{{ route('admin.reports.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $active === 'election' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Election Reports</a>
    <a href="{{ route('admin.reports.talent') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $active === 'talent' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Talent Reports</a>
    <a href="{{ route('admin.reports.fundraising') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $active === 'fundraising' ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">Fundraising Reports</a>
</div>
