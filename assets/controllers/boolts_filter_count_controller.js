// assets/controllers/boolts_filter_count_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'button',
        'count',
        'label',
    ];

    static values = {
        url: String,
    };

    connect() {
        this.timeout = null;
        this.abortController = null;
        this.isResetting = false;

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
        if (!this.hasUrlValue || this.isResetting) {
            return;
        }

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.timeout = setTimeout(() => {
            this.loadCount();
        }, 350);
    }

    refreshAfterReset(event) {
        this.isResetting = true;

        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }

        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }

        const form = event.currentTarget;

        window.requestAnimationFrame(() => {
            this.resetNatureInputs(form);
            this.resetPropertyTypeInputs(form);
            this.resetDpeInputs(form);
            this.resetNumericInputs(form);
            this.resetSearchInputs(form);
            this.resetLocationFields(form);
            this.resetChoiceLabels(form);

            window.dispatchEvent(
                new CustomEvent('boolts-location:reset')
            );

            setTimeout(() => {
                this.isResetting = false;
                this.loadCount();
            }, 0);
        });
    }

    resetNatureInputs(form) {
        const natureInputs = form.querySelectorAll(
            'input[name="modal_filter[natureDeLaPropriete]"]'
        );

        natureInputs.forEach((input) => {
            input.checked = input.value === '';
        });
    }

    resetPropertyTypeInputs(form) {
        const propertyTypeInputs = form.querySelectorAll(
            'input[name="modal_filter[typeDePropriete][]"]'
        );

        propertyTypeInputs.forEach((input) => {
            input.checked = false;
        });
    }

    resetDpeInputs(form) {
        const dpeInputs = form.querySelectorAll(
            'input[name="modal_filter[dpe][]"]'
        );

        dpeInputs.forEach((input) => {
            input.checked = false;
        });
    }

    resetNumericInputs(form) {
        const numericInputs = form.querySelectorAll(
            'input[type="number"]'
        );

        numericInputs.forEach((input) => {
            input.value = '';
        });
    }

    resetSearchInputs(form) {
        const searchInputs = form.querySelectorAll(
            'input[type="search"]'
        );

        searchInputs.forEach((input) => {
            input.value = '';
        });
    }

    resetLocationFields(form) {
        const locationFieldNames = [
            'modal_filter[pays]',
            'modal_filter[ville]',
            'modal_filter[quartier]',
        ];

        locationFieldNames.forEach((fieldName) => {
            const input = form.querySelector(
                `input[name="${fieldName}"]`
            );

            if (!input) {
                return;
            }

            input.value = '[]';

            input.dispatchEvent(
                new Event('change', {
                    bubbles: true,
                })
            );
        });

        form
            .querySelectorAll('.boolts-selected-list')
            .forEach((list) => {
                list.innerHTML = '';
                list.hidden = true;
            });
    }

    resetChoiceLabels(form) {
        form
            .querySelectorAll('.boolts-choice-remove')
            .forEach((icon) => {
                icon.remove();
            });

        form
            .querySelectorAll('.boolts-choice')
            .forEach((label) => {
                label.classList.remove(
                    'active',
                    'selected',
                    'is-selected'
                );
            });
    }

    async loadCount() {
        if (!this.hasUrlValue) {
            return;
        }

        const formData = new FormData(this.element);
        const params = new URLSearchParams(formData);

        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();

        if (this.hasButtonTarget) {
            this.buttonTarget.classList.add('is-loading');
            this.buttonTarget.disabled = true;
        }

        try {
            const response = await fetch(
                `${this.urlValue}?${params.toString()}`,
                {
                    method: 'GET',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: this.abortController.signal,
                }
            );

            if (!response.ok) {
                throw new Error(
                    `Erreur HTTP ${response.status}`
                );
            }

            const data = await response.json();

            const total = Number(
                data.total
                ?? data.count
                ?? data.totalResults
                ?? 0
            );

            this.updateButton(total);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(
                    'Erreur AJAX count filtres :',
                    error
                );
            }
        } finally {
            if (this.hasButtonTarget) {
                this.buttonTarget.classList.remove('is-loading');
                this.buttonTarget.disabled = false;
            }
        }
    }

    updateButton(total) {
        if (this.hasCountTarget) {
            this.countTarget.textContent = new Intl.NumberFormat(
                'fr-FR'
            ).format(total);
        }

        if (this.hasLabelTarget) {
            this.labelTarget.textContent = total === 1
                ? 'logement'
                : 'logements';
        }
    }
}