const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.getElementById('recovery-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const status = document.getElementById('recovery-status');
    const submit = form.querySelector('button[type="submit"]');
    const label = document.getElementById('recovery-submit-label');
    const spinner = document.getElementById('recovery-spinner');
    const originalLabel = label?.textContent ?? 'Request Passkey Recovery';

    status.classList.add('hidden');
    status.classList.remove(
        'border-rose-400/30',
        'bg-rose-500/10',
        'text-rose-100',
        'border-cyan-400/30',
        'bg-cyan-500/10',
        'text-cyan-100',
        'border-amber-400/30',
        'bg-amber-500/10',
        'text-amber-100',
    );

    if (submit) {
        submit.disabled = true;
    }
    if (label) {
        label.textContent = 'Sending request…';
    }
    spinner?.classList.remove('hidden');

    try {
        const response = await fetch(form.dataset.url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({
                account_id: form.account_id.value,
                email: form.email.value,
            }),
        });

        const data = await response.json().catch(() => ({}));
        let message = data.message ?? 'Request submitted.';

        if (! response.ok && data.errors) {
            const first = Object.values(data.errors).flat()[0];
            if (first) {
                message = first;
            }
        }

        status.textContent = message;
        status.classList.remove('hidden');

        if (response.status === 422 || response.status === 429) {
            status.classList.add('border-rose-400/30', 'bg-rose-500/10', 'text-rose-100');
        } else if (response.status === 503 || data.delivery_failed) {
            status.classList.add('border-amber-400/30', 'bg-amber-500/10', 'text-amber-100');
        } else {
            status.classList.add('border-cyan-400/30', 'bg-cyan-500/10', 'text-cyan-100');
        }
    } catch {
        status.textContent = 'Unable to submit the request. Please try again.';
        status.classList.remove('hidden');
        status.classList.add('border-rose-400/30', 'bg-rose-500/10', 'text-rose-100');
    } finally {
        if (submit) {
            submit.disabled = false;
        }
        if (label) {
            label.textContent = originalLabel;
        }
        spinner?.classList.add('hidden');
    }
});
