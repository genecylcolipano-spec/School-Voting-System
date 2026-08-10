const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.getElementById('recovery-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const status = document.getElementById('recovery-status');
    const submit = form.querySelector('button[type="submit"]');

    status.classList.add('hidden');
    status.classList.remove(
        'border-rose-200',
        'bg-rose-50',
        'text-rose-800',
        'border-green-200',
        'bg-green-50',
        'text-green-800',
        'border-amber-200',
        'bg-amber-50',
        'text-amber-900',
    );

    if (submit) {
        submit.disabled = true;
    }

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
            status.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
        } else if (response.status === 503 || data.delivery_failed) {
            status.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
        } else {
            status.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        }
    } catch {
        status.textContent = 'Unable to submit the request. Please try again.';
        status.classList.remove('hidden');
        status.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
    } finally {
        if (submit) {
            submit.disabled = false;
        }
    }
});
