const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

document.getElementById('recovery-form')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const status = document.getElementById('recovery-status');

    status.classList.remove('hidden', 'border-rose-200', 'text-rose-800', 'border-green-200', 'text-green-800');
    status.classList.add('border-green-200', 'bg-green-50', 'text-green-800');

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
    status.textContent = data.message ?? 'Request submitted.';
    status.classList.remove('hidden');
});
