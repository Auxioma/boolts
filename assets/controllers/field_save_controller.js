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
        const isAppend = button.dataset.fieldSaveAppendParam === 'true';

        const inputSelector = button.dataset.fieldSaveInputParam;
        const input = inputSelector
            ? document.querySelector(inputSelector)
            : field.querySelector('input, textarea, select');

        const finalValue = this.getFinalValue(field, input, isMultiple);
        const initialValue = field.dataset.initialValue ?? this.serializeValue(finalValue);

        const isEditing = field.dataset.editing === 'true';

        const errorMessage = field.querySelector('.js-field-error');

        if (!isEditing) {

            field.dataset.initialValue = this.serializeValue(finalValue);

            if (input) {
                input.dataset.initialValue = this.serializeValue(finalValue);
            }

            if (!isAppend && value) {
                value.classList.add('d-none');
            }

            if (inputWrapper) {
                inputWrapper.classList.remove('d-none');
            }

            this.setButtonState(button, 'editing');

            field.dataset.editing = 'true';

            if (input) {
                input.focus();
            }

            return;
        }

        const whatsAppValue = this.getWhatsAppValue(button, finalValue);

        const payload = {
            field: fieldName,
            value: finalValue,
            _token: this.csrfValue
        };

        if (whatsAppValue !== undefined) {
            payload.whatsApp = whatsAppValue;
        }

        if (
            this.serializeValue(finalValue) === initialValue
            && whatsAppValue === undefined
        ) {

            if (!isAppend && value) {
                value.classList.remove('d-none');
            }

            if (inputWrapper) {
                inputWrapper.classList.add('d-none');
            }

            this.setButtonState(button, 'closed');

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
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || data.success === false) {

                if (errorMessage) {

                    errorMessage.textContent = data.message || 'Une erreur est survenue.';

                    errorMessage.classList.remove('d-none');

                    clearTimeout(errorMessage.hideTimeout);

                    errorMessage.hideTimeout = setTimeout(() => {
                        errorMessage.classList.add('d-none');
                        errorMessage.textContent = '';
                    }, 3000);
                }

                return;
            }

            if (data.success) {

                window.dispatchEvent(new CustomEvent('profile:field-saved', {
                    detail: {
                        field: fieldName,
                        value: finalValue,
                        whatsApp: whatsAppValue
                    }
                }));

                if (output) {
                    output.textContent = this.formatOutput(finalValue, isMultiple);
                }

                if (!isAppend && value) {
                    value.classList.remove('d-none');
                }

                if (inputWrapper) {
                    inputWrapper.classList.add('d-none');
                }

                this.setButtonState(button, 'closed');

                field.dataset.editing = 'false';

                field.dataset.initialValue = this.serializeValue(finalValue);

                if (input) {
                    input.dataset.initialValue = this.serializeValue(finalValue);
                }
            }

        } catch (e) {

            console.error(e);

            if (errorMessage) {
                errorMessage.textContent = 'Erreur de communication avec le serveur.';
                errorMessage.classList.remove('d-none');
            }

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
                        this.setButtonState(button, 'save');
                    } else {
                        this.setButtonState(button, 'editing');
                    }
                });
            });
        });
    }

    getWhatsAppValue(button, phoneValue) {

        const whatsappSwitchSelector = button.dataset.fieldSaveWhatsappSwitchParam;
        const sameNumberSelector = button.dataset.fieldSaveSameNumberParam;
        const whatsappInputSelector = button.dataset.fieldSaveWhatsappInputParam;

        if (!whatsappSwitchSelector) {
            return undefined;
        }

        const whatsappSwitch = document.querySelector(whatsappSwitchSelector);
        const sameNumber = document.querySelector(sameNumberSelector);
        const whatsappInput = document.querySelector(whatsappInputSelector);

        if (!whatsappSwitch || !whatsappSwitch.checked) {
            return null;
        }

        if (sameNumber && sameNumber.checked) {
            return phoneValue || null;
        }

        if (whatsappInput) {
            const whatsAppValue = whatsappInput.dataset.fullPhoneValue ?? whatsappInput.value;

            return whatsAppValue || null;
        }

        return null;
    }

    setButtonState(button, state) {

        if (button.classList.contains('js-btn-pen') || button.classList.contains('js-btn-save')) {
            return;
        }

        const labels = {
            editing: 'Fermer',
            closed: 'Modifier',
            save: 'Enregistrer'
        };

        button.textContent = labels[state];
    }

    getFinalValue(field, input, isMultiple) {

        if (isMultiple) {
            const values = {};

            field.querySelectorAll('input, textarea, select').forEach((item) => {
                const name = item.dataset.name || item.name;

                values[name] = item.dataset.fullPhoneValue ?? item.value;
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

        if (value.adresse || value.codePostal || value.ville || value.pays) {
            return [
                value.adresse || '',
                value.adresseComplement || '',
                `${value.codePostal || ''} ${value.ville || ''}`.trim(),
                value.pays || ''
            ].filter(Boolean).join(', ') || '***';
        }

        if (value.adresseContact || value.codePostalContact || value.villeContact || value.paysContact) {
            return [
                value.adresseContact || '',
                `${value.codePostalContact || ''} ${value.villeContact || ''}`.trim(),
                value.paysContact || ''
            ].filter(Boolean).join(', ') || '***';
        }

        return '***';
    }
}
