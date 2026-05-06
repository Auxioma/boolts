import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String
    }

    static targets = ['success', 'error']

    async save(event) {
        const field = event.params.field;
        const input = document.querySelector(event.params.input);
        const output = document.querySelector(event.params.output);

        this.successTarget.classList.add('d-none');
        this.errorTarget.classList.add('d-none');

        const response = await fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                field: field,
                value: input.value
            })
        });

        const text = await response.text();

        let data = {};

        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Réponse Symfony non JSON :', text);
            this.errorTarget.textContent = 'Erreur serveur. Regarde la console Symfony.';
            this.errorTarget.classList.remove('d-none');
            return;
        }

        if (!response.ok || data.success !== true) {
            this.errorTarget.textContent = data.message ?? 'Une erreur est survenue.';
            this.errorTarget.classList.remove('d-none');
            return;
        }

        output.textContent = data.value || '***';
        this.successTarget.classList.remove('d-none');
    }
}
