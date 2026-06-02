// assets/controllers/mes_biens/submit_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['submit'];

    connect() {
        this.refresh = this.refresh.bind(this);

        this.element.addEventListener('change', this.refresh);
        this.element.addEventListener('input', this.refresh);

        this.refresh();
    }

    disconnect() {
        this.element.removeEventListener('change', this.refresh);
        this.element.removeEventListener('input', this.refresh);
    }

    refresh() {
        if (!this.hasSubmitTarget) {
            return;
        }

        const isValid =
            this.areRequiredFieldsValid()
            && this.areRequiredGroupsValid()
            && this.areRequiredMinValuesValid()
            && this.areRequiredImagesValid();

        this.submitTarget.disabled = !isValid;

        if (isValid) {
            this.submitTarget.classList.remove('is-disabled');
        } else {
            this.submitTarget.classList.add('is-disabled');
        }
    }

    areRequiredFieldsValid() {
        const fields = Array.from(
            this.element.querySelectorAll('[data-mes-biens-required-field]')
        );

        if (fields.length === 0) {
            return true;
        }

        return fields.every((field) => {
            if (field.disabled) {
                return true;
            }

            if (field.type === 'radio' || field.type === 'checkbox') {
                return field.checked;
            }

            return String(field.value || '').trim() !== '';
        });
    }

    areRequiredGroupsValid() {
        const groupElements = Array.from(
            this.element.querySelectorAll('[data-mes-biens-required-group]')
        );

        if (groupElements.length === 0) {
            return true;
        }

        const groupNames = [
            ...new Set(
                groupElements.map((element) => {
                    return element.dataset.mesBiensRequiredGroup;
                })
            ),
        ];

        return groupNames.every((groupName) => {
            const fields = Array.from(
                this.element.querySelectorAll(`[data-mes-biens-required-group="${groupName}"]`)
            );

            return fields.some((field) => {
                if (field.disabled) {
                    return false;
                }

                if (field.type === 'radio' || field.type === 'checkbox') {
                    return field.checked;
                }

                return String(field.value || '').trim() !== '';
            });
        });
    }

    areRequiredMinValuesValid() {
        const fields = Array.from(
            this.element.querySelectorAll('[data-mes-biens-required-min-value]')
        );

        if (fields.length === 0) {
            return true;
        }

        return fields.every((field) => {
            if (field.disabled) {
                return true;
            }

            const minValue = parseFloat(
                String(field.dataset.mesBiensRequiredMinValue || '0').replace(',', '.')
            );

            const currentValue = parseFloat(
                String(field.value || '0').replace(',', '.')
            );

            if (Number.isNaN(currentValue)) {
                return false;
            }

            return currentValue >= minValue;
        });
    }

    areRequiredImagesValid() {
        const imageContainers = Array.from(
            this.element.querySelectorAll('[data-mes-biens-required-images]')
        );

        if (imageContainers.length === 0) {
            return true;
        }

        return imageContainers.every((container) => {
            const minImages = parseInt(
                container.dataset.mesBiensRequiredImages || '1',
                10
            );

            const filledImages = container.querySelectorAll(
                '.property-preview-card.is-filled'
            ).length;

            return filledImages >= minImages;
        });
    }
}
