document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-boost-action]');

    if (!button || !button.closest('[data-admin-agency-boosts]')) {
        return;
    }

    event.preventDefault();

    if (button.dataset.confirm && !window.confirm(button.dataset.confirm)) {
        return;
    }

    const container = button.closest('[data-admin-agency-boosts]');
    const row = button.closest('[data-boost-row]');
    const errorEl = container.querySelector('[data-boost-error]');
    const rowButtons = row.querySelectorAll('button');

    const formData = new FormData();
    formData.append(button.dataset.tokenName, button.dataset.token);

    rowButtons.forEach((element) => {
        element.disabled = true;
    });
    showError(errorEl, '');

    try {
        const response = await fetch(button.dataset.url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.redirected) {
            throw new Error('Votre session administrateur a expiré ou a été remplacée. Veuillez vous reconnecter à l’administration.');
        }

        const contentType = response.headers.get('content-type') ?? '';

        if (!contentType.includes('application/json')) {
            throw new Error('Le serveur n’a pas renvoyé une réponse JSON valide.');
        }

        const result = await response.json();

        if (!response.ok || result.success !== true) {
            throw new Error(result.message ?? 'L’opération a échoué.');
        }

        row.remove();

        if (!container.querySelector('[data-boost-row]')) {
            container.innerHTML = '<div class="text-body-secondary">Aucun boost actif pour cette agence.</div>';
        }
    } catch (error) {
        showError(errorEl, error.message ?? 'L’opération a échoué.');
        rowButtons.forEach((element) => {
            element.disabled = false;
        });
    }
});

function showError(errorEl, message) {
    if (!errorEl) {
        return;
    }

    errorEl.textContent = message;
    errorEl.classList.toggle('d-none', !message);
}
