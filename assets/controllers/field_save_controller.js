import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static values = {
        url: String,
        csrf: String
    }

    async handle(event) {

        const button = event.currentTarget;

        const field = button.closest('.js-editable-field');

        const value = field.querySelector('.js-field-value');
        const inputWrapper = field.querySelector('.js-field-input');

        const fieldName = button.dataset.fieldSaveFieldParam;

        const outputSelector = button.dataset.fieldSaveOutputParam;
        const output = document.querySelector(outputSelector);

        const isMultiple = button.dataset.fieldSaveMultipleParam === 'true';

        const inputSelector = button.dataset.fieldSaveInputParam;
        const input = inputSelector
            ? document.querySelector(inputSelector)
            : field.querySelector('input, textarea, select');

        const finalValue = this.getFinalValue(field, input, isMultiple);
        const initialValue = field.dataset.initialValue ?? this.serializeValue(finalValue);

        const isEditing = field.dataset.editing === 'true';

        if (!isEditing) {

            field.dataset.initialValue = this.serializeValue(finalValue);

            if (input) {
                input.dataset.initialValue = this.serializeValue(finalValue);
            }

            value.classList.add('d-none');

            inputWrapper.classList.remove('d-none');

            this.setButtonState(button, 'editing'); // ← 'Fermer'

            field.dataset.editing = 'true';

            if (input) {
                input.focus();
            }

            return;
        }

        if (this.serializeValue(finalValue) === initialValue) {

            value.classList.remove('d-none');

            inputWrapper.classList.add('d-none');

            this.setButtonState(button, 'closed'); // ← 'Modifier'

            field.dataset.editing = 'false';

            return;
        }

        try {

            button.disabled = true;

            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: fieldName,
                    value: finalValue,
                    _token: this.csrfValue
                })
            });

            const data = await response.json();

            if (data.success) {

                output.textContent = this.formatOutput(finalValue, isMultiple);

                value.classList.remove('d-none');

                inputWrapper.classList.add('d-none');

                this.setButtonState(button, 'closed'); // ← 'Modifier'

                field.dataset.editing = 'false';

                field.dataset.initialValue = this.serializeValue(finalValue);

                if (input) {
                    input.dataset.initialValue = this.serializeValue(finalValue);
                }
            }

        } catch (e) {

            console.error(e);

        } finally {

            button.disabled = false;
        }
    }

    connect() {

        document.querySelectorAll('.js-editable-field').forEach((field) => {

            const button = field.querySelector('.js-edit-button');

            if (!button) {
                return;
            }

            const isMultiple = button.dataset.fieldSaveMultipleParam === 'true';

            const inputSelector = button.dataset.fieldSaveInputParam;
            const input = inputSelector
                ? document.querySelector(inputSelector)
                : field.querySelector('input, textarea, select');

            const inputs = isMultiple
                ? field.querySelectorAll('input, textarea, select')
                : [input];

            const initialValue = this.getFinalValue(field, input, isMultiple);

            field.dataset.initialValue = this.serializeValue(initialValue);

            inputs.forEach((item) => {

                if (!item) {
                    return;
                }

                item.addEventListener('input', () => {

                    if (field.dataset.editing !== 'true') {
                        return;
                    }

                    const currentValue = this.getFinalValue(field, input, isMultiple);

                    if (this.serializeValue(currentValue) !== field.dataset.initialValue) {
                        this.setButtonState(button, 'save'); // ← 'Enregistrer'
                    } else {
                        this.setButtonState(button, 'editing'); // ← 'Fermer'
                    }
                });
            });
        });
    }

    // ↓ SEUL AJOUT — tout le reste est intact
    setButtonState(button, state) {

        const iconPen  = button.querySelector('.js-icon-pen');
        const iconSave = button.querySelector('.js-icon-save');

        if (iconPen && iconSave) {
            const isOpen = state === 'editing' || state === 'save';
            iconPen.classList.toggle('d-none', isOpen);
            iconSave.classList.toggle('d-none', !isOpen);
            return;
        }

        // Comportement texte natif inchangé
        const labels = { editing: 'Fermer', closed: 'Modifier', save: 'Enregistrer' };
        button.textContent = labels[state];
    }

    getFinalValue(field, input, isMultiple) {

        if (isMultiple) {
            const values = {};

            field.querySelectorAll('input, textarea, select').forEach((item) => {
                const name = item.dataset.name || item.name;

                values[name] = item.value;
            });

            return values;
        }

        if (!input) {
            return '';
        }

        return input.dataset.fullPhoneValue ?? input.value;
    }

    serializeValue(value) {
        return typeof value === 'object'
            ? JSON.stringify(value)
            : String(value ?? '');
    }

    formatOutput(value, isMultiple) {

        if (!isMultiple) {
            return value || '***';
        }

        const adresse = value.adresse || '';
        const adresseComplement = value.adresseComplement || '';
        const codePostal = value.codePostal || '';
        const ville = value.ville || '';
        const pays = value.pays || '';

        return [
            adresse,
            adresseComplement,
            `${codePostal} ${ville}`.trim(),
            pays
        ].filter(Boolean).join(', ') || '***';
    }
}