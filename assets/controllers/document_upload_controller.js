import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'status'];

    async submit() {
        if (!this.inputTarget.files.length) {
            return;
        }

        this.inputTarget.disabled = true;
        this.statusTarget.textContent = 'Téléversement en cours…';

        try {
            const response = await fetch(this.element.action, {
                method: this.element.method,
                body: new FormData(this.element),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message ?? 'Le téléversement a échoué.');
            }

            this.statusTarget.textContent = 'Téléversé';

            if (result.documentsComplete) {
                document.querySelector('[data-documents-continue]')?.removeAttribute('disabled');
            }
        } catch (error) {
            this.inputTarget.disabled = false;
            this.statusTarget.textContent = error.message ?? 'Erreur de téléversement';
        }
    }
}
