/**
 * Shared admin confirmation modal for sensitive forms (delete, publish, etc.).
 */

const DEFAULT_CONFIRM_MSG = 'This will be logged and visible to Super Admins. Proceed?';

export function showConfirmModal(title, message, options = {}) {
    const modal = document.getElementById('admin-confirm-modal');
    const titleEl = document.getElementById('admin-confirm-title');
    const messageEl = document.getElementById('admin-confirm-message');
    const okBtn = modal?.querySelector('[data-confirm-ok]');
    const cancelBtn = modal?.querySelector('[data-confirm-cancel]');

    if (!modal || !titleEl || !messageEl || !okBtn) {
        return Promise.resolve(window.confirm(message || DEFAULT_CONFIRM_MSG));
    }

    const okLabel = options.okLabel || 'Proceed';
    const isDanger = Boolean(options.danger);
    const defaultOkClass = 'rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500';
    const dangerOkClass = 'rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500';

    titleEl.textContent = title || 'Confirm action';
    messageEl.textContent = message || DEFAULT_CONFIRM_MSG;
    okBtn.textContent = okLabel;
    okBtn.className = isDanger ? dangerOkClass : defaultOkClass;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    return new Promise((resolve) => {
        const cleanup = (result) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            okBtn.removeEventListener('click', onOk);
            cancelBtn?.removeEventListener('click', onCancel);
            modal.removeEventListener('click', onBackdrop);
            document.removeEventListener('keydown', onKeydown);
            resolve(result);
        };

        const onOk = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onBackdrop = (event) => {
            if (event.target === modal) {
                cleanup(false);
            }
        };
        const onKeydown = (event) => {
            if (event.key === 'Escape') {
                cleanup(false);
            }
        };

        okBtn.addEventListener('click', onOk);
        cancelBtn?.addEventListener('click', onCancel);
        modal.addEventListener('click', onBackdrop);
        document.addEventListener('keydown', onKeydown);
        cancelBtn?.focus();
    });
}

export function initAdminConfirmations(root = document) {
    root.querySelectorAll('[data-confirm-sensitive]').forEach((form) => {
        if (form.dataset.confirmBound === '1') {
            return;
        }

        form.dataset.confirmBound = '1';

        const handler = async (event) => {
            event.preventDefault();
            const title = form.dataset.confirmTitle || 'Confirm action';
            const message = form.dataset.confirmMessage || DEFAULT_CONFIRM_MSG;
            const confirmed = await showConfirmModal(title, message, {
                okLabel: form.dataset.confirmOkLabel || 'Proceed',
                danger: form.dataset.confirmDanger === '1',
            });

            if (confirmed) {
                form.removeEventListener('submit', handler);
                form.submit();
            }
        };

        form.addEventListener('submit', handler);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initAdminConfirmations();
});
