import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        csrf: String
    }

    async handle(event) {
        event.preventDefault();

        const button = event.currentTarget;
        const field = button.closest('.js-editable-field');

        if (!field) {
            return;
        }

        const value = field.querySelector('.js-field-value');
        const inputWrapper = field.querySelector('.js-field-input');

        const fieldName = button.dataset.fieldSaveFieldParam;
        const isOpeningHours = this.isOpeningHoursField(fieldName);
        const isLanguages = fieldName === 'langueParlers';

        const outputSelector = button.dataset.fieldSaveOutputParam;
        const output = outputSelector ? document.querySelector(outputSelector) : null;

        const isMultiple = button.dataset.fieldSaveMultipleParam === 'true';
        const isAppend = button.dataset.fieldSaveAppendParam === 'true';

        const inputSelector = button.dataset.fieldSaveInputParam;
        const input = inputSelector
            ? field.querySelector(inputSelector) ?? document.querySelector(inputSelector)
            : field.querySelector('input, textarea, select');

        const openingHoursContainer = field.querySelector('.opening-hours') ?? inputWrapper ?? field;

        const finalValue = isOpeningHours
            ? this.getOpeningHoursValue(openingHoursContainer)
            : isLanguages
                ? this.getSelectedLanguagesValue(input)
                : this.getFinalValue(field, input, isMultiple);

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

            if (!isOpeningHours && input && typeof input.focus === 'function') {
                input.focus();
            }

            return;
        }

        const whatsAppValue = this.getWhatsAppValue(button, finalValue);

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

        if (isOpeningHours && !this.hasAtLeastOneOpeningHour(openingHoursContainer)) {
            this.showFieldError(
                errorMessage,
                'Veuillez renseigner au moins un horaire.'
            );

            return;
        }

        const payload = {
            field: fieldName,
            value: finalValue,
            _token: this.csrfValue
        };

        if (whatsAppValue !== undefined) {
            payload.whatsApp = whatsAppValue;
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
                this.showFieldError(
                    errorMessage,
                    data.message || 'Une erreur est survenue.'
                );

                return;
            }

            if (data.success) {
                this.showSuccessMessage(
                    data.message || 'Les modifications ont été effectuées avec succès !'
                );

                window.dispatchEvent(new CustomEvent('profile:field-saved', {
                    detail: {
                        field: fieldName,
                        value: finalValue,
                        whatsApp: whatsAppValue,
                        publicProfileUrl: data.publicProfileUrl ?? null
                    }
                }));

                if (output) {
                    output.textContent = this.formatOutput(finalValue, isMultiple, fieldName, input);
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

            this.showFieldError(
                errorMessage,
                'Erreur de communication avec le serveur.'
            );

        } finally {
            button.disabled = false;
        }
    }

    connect() {
        this.element.querySelectorAll('.js-editable-field').forEach((field) => {
            const button = field.querySelector('.js-edit-button');

            if (!button) {
                return;
            }

            const fieldName = button.dataset.fieldSaveFieldParam;
            const isOpeningHours = this.isOpeningHoursField(fieldName);
            const isLanguages = fieldName === 'langueParlers';
            const isMultiple = button.dataset.fieldSaveMultipleParam === 'true';

            const inputSelector = button.dataset.fieldSaveInputParam;
            const input = inputSelector
                ? field.querySelector(inputSelector) ?? document.querySelector(inputSelector)
                : field.querySelector('input, textarea, select');

            const openingHoursContainer = field.querySelector('.opening-hours') ?? field;

            const inputs = isOpeningHours
                ? openingHoursContainer.querySelectorAll('input[type="time"], .js-opening-toggle')
                : isLanguages && input
                    ? [input]
                    : isMultiple
                        ? field.querySelectorAll('input, textarea, select')
                        : [input];

            const initialValue = isOpeningHours
                ? this.getOpeningHoursValue(openingHoursContainer)
                : isLanguages
                    ? this.getSelectedLanguagesValue(input)
                    : this.getFinalValue(field, input, isMultiple);

            field.dataset.initialValue = this.serializeValue(initialValue);

            inputs.forEach((item) => {
                if (!item) {
                    return;
                }

                const refreshButtonState = () => {
                    if (field.dataset.editing !== 'true') {
                        return;
                    }

                    if (isOpeningHours) {
                        const currentValue = this.getOpeningHoursValue(openingHoursContainer);
                        const hasChanged = this.serializeValue(currentValue) !== field.dataset.initialValue;
                        const hasAtLeastOneHour = this.hasAtLeastOneOpeningHour(openingHoursContainer);

                        this.setButtonState(button, hasChanged && hasAtLeastOneHour ? 'save' : 'editing');

                        return;
                    }

                    const currentValue = isLanguages
                        ? this.getSelectedLanguagesValue(input)
                        : this.getFinalValue(field, input, isMultiple);

                    this.setButtonState(
                        button,
                        this.serializeValue(currentValue) !== field.dataset.initialValue
                            ? 'save'
                            : 'editing'
                    );
                };

                item.addEventListener('input', refreshButtonState);
                item.addEventListener('change', refreshButtonState);
            });
        });
    }

    getSelectedLanguagesValue(input) {
        if (!input || input.tagName !== 'SELECT') {
            return [];
        }

        return Array.from(input.selectedOptions).map((option) => option.value);
    }

    getSelectedLanguagesLabels(input) {
        if (!input || input.tagName !== 'SELECT') {
            return [];
        }

        return Array.from(input.selectedOptions).map((option) => option.textContent.trim());
    }

    isOpeningHoursField(fieldName) {
        return fieldName === 'openingHours'
            || fieldName === 'horaireOuvertures'
            || fieldName === 'horaireOuverture';
    }

    hasAtLeastOneOpeningHour(container) {
        return Array.from(container.querySelectorAll('input[type="time"]'))
            .some((input) => input.value.trim() !== '');
    }

    getOpeningHoursValue(container) {
        const days = [
            'lundi',
            'mardi',
            'mercredi',
            'jeudi',
            'vendredi',
            'samedi',
            'dimanche',
        ];

        const values = {};

        container.querySelectorAll('[data-opening-hours-day]').forEach((dayElement, index) => {
            const day = days[index];

            if (!day) {
                return;
            }

            const toggle = dayElement.querySelector('.js-opening-toggle');
            const timeInputs = dayElement.querySelectorAll('input[type="time"]');

            values[day] = {
                jour: day,
                isOpen: toggle && toggle.checked ? '1' : '0',
                ouvertureMatin: timeInputs[0]?.value ?? '',
                fermetureMatin: timeInputs[1]?.value ?? '',
                ouvertureApresMidi: timeInputs[2]?.value ?? '',
                fermetureApresMidi: timeInputs[3]?.value ?? '',
            };
        });

        return values;
    }

    formatOpeningHoursOutput(value) {
        const labels = {
            lundi: 'Lundi',
            mardi: 'Mardi',
            mercredi: 'Mercredi',
            jeudi: 'Jeudi',
            vendredi: 'Vendredi',
            samedi: 'Samedi',
            dimanche: 'Dimanche',
        };

        const result = [];

        Object.entries(value).forEach(([day, hours]) => {
            const slots = [];

            if (hours.ouvertureMatin || hours.fermetureMatin) {
                slots.push(`${hours.ouvertureMatin || '--:--'} - ${hours.fermetureMatin || '--:--'}`);
            }

            if (hours.ouvertureApresMidi || hours.fermetureApresMidi) {
                slots.push(`${hours.ouvertureApresMidi || '--:--'} - ${hours.fermetureApresMidi || '--:--'}`);
            }

            if (slots.length > 0) {
                result.push(`${labels[day] || day} : ${slots.join(' / ')}`);
            }
        });

        return result.length > 0
            ? result.join(' | ')
            : 'Tous les jours sont fermés par défaut';
    }

    showSuccessMessage(message) {
        const successMessage = document.querySelector('.js-success-message');

        if (!successMessage) {
            return;
        }

        successMessage.textContent = message;
        successMessage.classList.remove('d-none');

        clearTimeout(successMessage.hideTimeout);

        successMessage.hideTimeout = setTimeout(() => {
            successMessage.classList.add('d-none');
            successMessage.textContent = '';
        }, 3000);
    }

    showFieldError(errorMessage, message) {
        if (!errorMessage) {
            return;
        }

        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');

        clearTimeout(errorMessage.hideTimeout);

        errorMessage.hideTimeout = setTimeout(() => {
            errorMessage.classList.add('d-none');
            errorMessage.textContent = '';
        }, 3000);
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
            editing: 'Annuler',
            closed: 'Modifier',
            save: 'Enregistrer'
        };

        button.dataset.fieldSaveState = state;
        button.textContent = labels[state];
    }

    getFinalValue(field, input, isMultiple) {
        if (isMultiple) {
            const values = {};

            field.querySelectorAll('input, textarea, select').forEach((item) => {
                const name = item.dataset.name || item.name;

                if (item.tagName === 'SELECT' && item.multiple) {
                    values[name] = Array.from(item.selectedOptions).map((option) => option.value);
                    return;
                }

                values[name] = item.dataset.fullPhoneValue ?? item.value;
            });

            return values;
        }

        if (!input) {
            return '';
        }

        if (input.tagName === 'SELECT' && input.multiple) {
            return Array.from(input.selectedOptions).map((option) => option.value);
        }

        return input.dataset.fullPhoneValue ?? input.value;
    }

    serializeValue(value) {
        return typeof value === 'object'
            ? JSON.stringify(value)
            : String(value ?? '');
    }

    formatOutput(value, isMultiple, fieldName = null, input = null) {
        if (fieldName === 'langueParlers') {
            const labels = this.getSelectedLanguagesLabels(input);

            return labels.length > 0
                ? labels.join(', ')
                : 'Définissez les langues parlées par l’agence';
        }

        if (this.isOpeningHoursField(fieldName)) {
            return this.formatOpeningHoursOutput(value);
        }

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
