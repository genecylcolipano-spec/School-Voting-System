<section id="overview" class="scroll-mt-28 space-y-4">
    <div class="flex items-center justify-end gap-2">
        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
        <p id="dashboard-live-updated" class="text-[11px] font-medium text-slate-500">Live · syncing…</p>
    </div>

    {{-- Top bento: hero + metric grid --}}
    <div class="grid gap-4 xl:grid-cols-12">
        <div class="xl:col-span-5">
            @include('admin.dashboard._hero')
        </div>
        <div class="xl:col-span-7">
            @include('admin.dashboard._stats')
        </div>
    </div>

    {{-- Analytics widgets --}}
    @include('admin.dashboard._analytics-widgets')

    {{-- Voting Management: full width edge-to-edge --}}
    <div class="-mx-4 sm:-mx-6 lg:-mx-8">
        @include('admin.dashboard._live-voting')
    </div>
</section>
