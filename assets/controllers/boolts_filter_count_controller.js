// assets/controllers/boolts_filter_count_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'count', 'label'];

    static values = {
        url: String,
    };

    connect() {
        this.timeout = null;
        this.abortController = null;

        this.refresh();
    }

    disconnect() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        if (this.abortController) {
            this.abortController.abort();
        }
    }

    refresh() {
        if (!this.hasUrlValue) {
            return;
        }

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(() => {
            this.loadCount();
        }, 350);
    }

    refreshAfterReset() {
        setTimeout(() => {
            this.refresh();
        }, 0);
    }

    async loadCount() {
        const formData = new FormData(this.element);
        const params = new URLSearchParams(formData);

        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();

        this.buttonTarget.classList.add('is-loading');

        try {
            const response = await fetch(`${this.urlValue}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const total = Number(data.total ?? 0);

            this.updateButton(total);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Erreur AJAX count filtres :', error);
            }
        } finally {
            this.buttonTarget.classList.remove('is-loading');
        }
    }

    updateButton(total) {
        this.countTarget.textContent = new Intl.NumberFormat('fr-FR').format(total);
        this.labelTarget.textContent = total === 1 ? 'logement' : 'logements';
    }
}