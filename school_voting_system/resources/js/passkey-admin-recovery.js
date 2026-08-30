const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const statusPanel = document.getElementById('recovery-admin-status');

function setStatus(message, kind = 'ok') {
    if (!statusPanel) {
        return;
    }

    statusPanel.classList.remove(
        'hidden',
        'border-green-200',
        'bg-green-50',
        'text-green-800',
        'border-rose-200',
        'bg-rose-50',
        'text-rose-800',
        'border-emerald-500/20',
        'bg-emerald-500/10',
        'text-emerald-200',
        'border-rose-500/20',
        'bg-rose-500/10',
        'text-rose-200',
    );

    if (kind === 'error') {
        statusPanel.classList.add('border-rose-500/20', 'bg-rose-500/10', 'text-rose-200');
    } else {
        statusPanel.classList.add('border-emerald-500/20', 'bg-emerald-500/10', 'text-emerald-200');
    }

    statusPanel.textContent = message;
}

async function postJson(url, body = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(body),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message ?? 'The request could not be completed.');
    }

    return data;
}

async function copyToClipboard(text) {
    if (!text) {
        return false;
    }

    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            return true;
        }
    } catch (error) {
        // Fall through to legacy copy method.
    }

    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', 'readonly');
    input.style.position = 'fixed';
    input.style.top = '-9999px';
    document.body.appendChild(input);
    input.focus();
    input.select();

    let copied = false;
    try {
        copied = document.execCommand('copy');
    } catch (error) {
        copied = false;
    }

    input.remove();
    return copied;
}

function removeRecoveryRows(id) {
    document.querySelectorAll(`[data-recovery-row="${id}"]`).forEach((row) => row.remove());

    if (!document.querySelector('[data-recovery-row]')) {
        document.querySelectorAll('[data-recovery-lists]').forEach((el) => el.classList.add('hidden'));
        document.querySelector('[data-recovery-empty]')?.classList.remove('hidden');
    }
}

document.querySelectorAll('[data-enroll-url], [data-reset-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        const confirmMessage = button.dataset.confirm;
        if (confirmMessage && !window.confirm(confirmMessage)) {
            return;
        }

        button.disabled = true;

        try {
            const url = button.dataset.enrollUrl || button.dataset.resetUrl;
            const result = await postJson(url, {
                recovery_request_id: Number(button.dataset.recoveryRequestId),
            });
            const copied = await copyToClipboard(result.enrollment_url);
            const recipient = result.recipient ? ` to ${result.recipient}` : '';
            if (result.email_sent) {
                setStatus(
                    copied
                        ? `Enrollment link emailed${recipient} and copied to clipboard.`
                        : `Enrollment link emailed${recipient}.`
                );
            } else if (result.email_error) {
                setStatus(
                    copied
                        ? `${result.email_error} Link copied to clipboard.`
                        : `${result.email_error} ${result.enrollment_url}`,
                    'error'
                );
            } else {
                setStatus(
                    copied
                        ? 'Enrollment link generated and copied to clipboard.'
                        : `Enrollment link generated: ${result.enrollment_url}`
                );
            }

            removeRecoveryRows(button.dataset.recoveryRequestId);
        } catch (error) {
            setStatus(error?.message ?? 'Could not generate enrollment link.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});

document.querySelectorAll('[data-dismiss-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (!window.confirm('Dismiss this recovery request? It will leave the pending queue.')) {
            return;
        }

        button.disabled = true;

        try {
            await postJson(button.dataset.dismissUrl);
            setStatus('Recovery request dismissed.');
            removeRecoveryRows(button.dataset.recoveryRequestId);
        } catch (error) {
            setStatus(error?.message ?? 'Could not dismiss this request.', 'error');
            button.disabled = false;
        }
    });
});
