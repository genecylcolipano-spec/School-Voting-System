<div
    id="admin-confirm-modal"
    class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="admin-confirm-title"
>
    <div class="w-full max-w-md rounded-2xl border border-violet-500/20 bg-slate-900 p-6 shadow-2xl">
        <h3 id="admin-confirm-title" class="text-lg font-semibold text-white">Confirm action</h3>
        <p id="admin-confirm-message" class="mt-2 whitespace-pre-line text-sm text-slate-400">This will be logged and visible to Super Admins. Proceed?</p>
        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                data-confirm-cancel
                class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
            >
                Cancel
            </button>
            <button
                type="button"
                data-confirm-ok
                class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500"
            >
                Proceed
            </button>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/confirm-modal.blade.php ENDPATH**/ ?>