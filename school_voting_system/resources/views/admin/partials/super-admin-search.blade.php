<div class="relative w-full" data-super-admin-search>
    <label class="sr-only" for="{{ $inputId }}">Search accounts, students, elections</label>
    <input
        id="{{ $inputId }}"
        type="search"
        placeholder="Search accounts, students, elections…"
        autocomplete="off"
        class="w-full rounded-xl border border-violet-500/20 bg-slate-900/80 px-4 py-2 text-sm text-white placeholder:text-slate-500 focus:border-violet-400/50 focus:outline-none"
    >
    <div
        data-super-admin-search-results
        class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-violet-500/20 bg-slate-900 shadow-xl"
    ></div>
</div>
