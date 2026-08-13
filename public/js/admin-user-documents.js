document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-document-action]');

    if (!button || !button.closest('[data-admin-user-documents]')) {
        return;
    }

    event.preventDefault();

    const card = button.closest('[data-document-card]');
    const action = button.dataset.documentAction;
    const reason = card.querySelector('[data-rejection-reason]')?.value.trim() ?? '';

    if (action === 'reject' && !reason) {
        showError(card, 'Le motif du refus est obligatoire.');
        return;
    }

    const formData = new FormData();
    formData.append(button.dataset.tokenName, button.dataset.token);

    if (action === 'reject') {
        formData.append(button.dataset.reasonName, reason);
    }

    setBusy(card, true);
    showError(card, '');

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
            throw new Error(result.message ?? 'La mise à jour du document a échoué.');
        }

        updateCard(card, result);
    } catch (error) {
        showError(card, error.message ?? 'La mise à jour du document a échoué.');
        setBusy(card, false);
    }
});

function updateCard(card, result) {
    const badge = card.querySelector('[data-document-status]');
    badge.textContent = result.statusLabel;
    badge.classList.remove('text-bg-secondary', 'text-bg-success', 'text-bg-danger');
    badge.classList.add(result.status === 'approved' ? 'text-bg-success' : 'text-bg-danger');

    const submissionStatus = card.querySelector('[data-submission-status]');

    if (submissionStatus) {
        submissionStatus.textContent = result.statusLabel;
        submissionStatus.classList.toggle('text-success', result.status === 'approved');
        submissionStatus.classList.toggle('fw-bold', result.status === 'approved');
    }

    card.querySelector('[data-document-actions]')?.remove();

    if (result.rejectionReason) {
        const reason = document.createElement('p');
        reason.className = 'text-danger mb-0 mt-3';
        reason.innerHTML = '<strong>Motif du refus :</strong> ';
        reason.append(document.createTextNode(result.rejectionReason));
        card.querySelector('[data-document-error]').before(reason);
    }
}

function setBusy(card, busy) {
    card.querySelectorAll('[data-document-actions] button').forEach((button) => {
        button.disabled = busy;
    });
}

function showError(card, message) {
    const error = card.querySelector('[data-document-error]');
    error.textContent = message;
    error.classList.toggle('d-none', !message);
}
