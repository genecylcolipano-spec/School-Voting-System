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
    );

    if (kind === 'error') {
        statusPanel.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
    } else {
        statusPanel.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
    }

    statusPanel.textContent = message;
}

async function issueLink(button) {
    const response = await fetch(button.dataset.resetUrl, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({
            recovery_request_id: Number(button.dataset.recoveryRequestId),
        }),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message ?? 'Could not generate enrollment link.');
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

document.querySelectorAll('[data-reset-url]').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;

        try {
            const result = await issueLink(button);
            const copied = await copyToClipboard(result.enrollment_url);
            if (result.email_sent) {
                setStatus(
                    copied
                        ? 'Enrollment link generated, email sent, and link copied to clipboard.'
                        : 'Enrollment link generated and email sent.'
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
            button.closest('tr')?.remove();
        } catch (error) {
            setStatus(error?.message ?? 'Could not generate enrollment link.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});

