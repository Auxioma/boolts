import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'title',
        'description',
        'button',
        'serviceModal',
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
            this.openServiceModal();
        } finally {
            this.setLoading(false, oldButtonText);
        }
    }

    openServiceModal() {
        if (!this.hasServiceModalTarget) {
            return;
        }

        this.serviceModalTarget.hidden = false;
        document.body.classList.add('ai-service-modal-open');

        this.closeServiceModalOnEscape = (event) => {
            if (event.key === 'Escape') {
                this.closeServiceModal();
            }
        };

        document.addEventListener('keydown', this.closeServiceModalOnEscape);

        const closeButton = this.serviceModalTarget.querySelector('[data-ai-service-modal-close]');

        if (closeButton) {
            closeButton.focus();
        }
    }

    closeServiceModal() {
        if (!this.hasServiceModalTarget) {
            return;
        }

        this.serviceModalTarget.hidden = true;
        document.body.classList.remove('ai-service-modal-open');

        if (this.closeServiceModalOnEscape) {
            document.removeEventListener('keydown', this.closeServiceModalOnEscape);
            this.closeServiceModalOnEscape = null;
        }
    }

    disconnect() {
        if (this.closeServiceModalOnEscape) {
            document.removeEventListener('keydown', this.closeServiceModalOnEscape);
            this.closeServiceModalOnEscape = null;
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
