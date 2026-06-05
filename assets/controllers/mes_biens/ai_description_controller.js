import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'title',
        'description',
        'button',
    ];

    static values = {
        url: String,
        csrfToken: String,
    };

    connect() {
        console.log('Contrôleur IA connecté');
    }

    async generate(event) {
        event.preventDefault();

        if (!this.hasUrlValue || !this.hasCsrfTokenValue || !this.hasDescriptionTarget) {
            console.error('Configuration IA incomplète.');
            return;
        }

        const oldButtonText = this.hasButtonTarget
            ? this.buttonTarget.innerHTML
            : 'Générer avec IA';

        this.setLoading(true);

        try {
            const form = this.element.closest('form');
            const formData = this.extractFormData(form);

            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    csrfToken: this.csrfTokenValue,
                    title: this.hasTitleTarget ? this.titleTarget.value : '',
                    currentDescription: this.descriptionTarget.value,
                    formData: formData,
                }),
            });

            const data = await response.json();

            if (!response.ok || data.success !== true) {
                throw new Error(data.message || 'Impossible de générer la description.');
            }

            this.descriptionTarget.value = data.description;

            this.descriptionTarget.dispatchEvent(new Event('input', {
                bubbles: true,
            }));

            this.descriptionTarget.dispatchEvent(new Event('change', {
                bubbles: true,
            }));
        } catch (error) {
            console.error(error);
            alert(error.message || 'Une erreur est survenue pendant la génération IA.');
        } finally {
            this.setLoading(false, oldButtonText);
        }
    }

    extractFormData(form) {
        const values = {};

        if (!form) {
            return values;
        }

        const formData = new FormData(form);

        for (const [name, value] of formData.entries()) {
            if (!name || value === null || value === '') {
                continue;
            }

            if (value instanceof File) {
                continue;
            }

            values[name] = value.toString().trim();
        }

        return values;
    }

    setLoading(isLoading, oldButtonText = null) {
        if (!this.hasButtonTarget) {
            return;
        }

        this.buttonTarget.disabled = isLoading;

        if (isLoading) {
            this.buttonTarget.innerHTML = 'Génération...';
            return;
        }

        if (oldButtonText !== null) {
            this.buttonTarget.innerHTML = oldButtonText;
        }
    }
}
