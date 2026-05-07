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

        const inputSelector = button.dataset.fieldSaveInputParam;

        const input = document.querySelector(inputSelector);

        const fieldName = button.dataset.fieldSaveFieldParam;

        const outputSelector = button.dataset.fieldSaveOutputParam;

        const output = document.querySelector(outputSelector);

        const initialValue = input.dataset.initialValue ?? input.value;

        const isEditing = field.dataset.editing === 'true';

        // OUVERTURE
        if (!isEditing) {

            field.dataset.initialValue = input.value;

            input.dataset.initialValue = input.value;

            value.classList.add('d-none');

            inputWrapper.classList.remove('d-none');

            button.textContent = 'Fermer';

            field.dataset.editing = 'true';

            input.focus();

            return;
        }

        // PAS MODIFIÉ → fermeture
        if (input.value === initialValue) {

            value.classList.remove('d-none');

            inputWrapper.classList.add('d-none');

            button.textContent = 'Modifier';

            field.dataset.editing = 'false';

            return;
        }

        // ENREGISTREMENT AJAX
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
                    value: input.value,
                    _token: this.csrfValue
                })
            });

            const data = await response.json();

            if (data.success) {

                output.textContent = input.value || '***';

                value.classList.remove('d-none');

                inputWrapper.classList.add('d-none');

                button.textContent = 'Modifier';

                field.dataset.editing = 'false';

                input.dataset.initialValue = input.value;
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

            const input = field.querySelector('input, textarea, select');

            if (!input) {
                return;
            }

            const initialValue = input.value;

            input.dataset.initialValue = initialValue;

            input.addEventListener('input', () => {

                if (field.dataset.editing !== 'true') {
                    return;
                }

                if (input.value !== input.dataset.initialValue) {

                    button.textContent = 'Enregistrer';

                } else {

                    button.textContent = 'Fermer';
                }
            });
        });
    }
}