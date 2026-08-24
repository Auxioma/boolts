import { Controller } from '@hotwired/stimulus';
import intlTelInput from 'intl-tel-input';

export default class extends Controller {
    static targets = ['input'];

    connect() {
        this.itiInstances = new Map();
        this.abortController = new AbortController();

        this.inputTargets.forEach((input) => {
            const iti = intlTelInput(input, {
                initialCountry: 'fr',
                countryOrder: ['fr', 'be', 'ch', 'cn'],
                separateDialCode: true,
                nationalMode: false,
            });

            this.itiInstances.set(input, iti);

            input.addEventListener('input', () => {
                this.updateFullNumber(input);
            }, { signal: this.abortController.signal });

            input.addEventListener('countrychange', () => {
                this.updateFullNumber(input);
            }, { signal: this.abortController.signal });

            this.updateFullNumber(input);
        });
    }

    disconnect() {
        this.abortController?.abort();

        this.itiInstances?.forEach((iti) => {
            iti.destroy();
        });

        this.itiInstances?.clear();
    }

    sync() {
        this.inputTargets.forEach((input) => {
            this.updateFullNumber(input);
        });
    }

    prepareSubmit() {
        this.inputTargets.forEach((input) => {
            input.value = this.updateFullNumber(input);
        });
    }

    updateFullNumber(input) {
        const iti = this.itiInstances.get(input);

        if (!iti) {
            return input.value.trim();
        }

        const countryData = iti.getSelectedCountryData();
        const dialCode = countryData.dialCode;
        const rawPhone = input.value.trim();

        if (!rawPhone) {
            input.dataset.fullPhoneValue = '';

            return '';
        }

        if (rawPhone.startsWith('+')) {
            const phone = rawPhone.slice(1).replace(/\D/g, '');
            const fullNumber = phone ? `+${phone}` : '';

            input.dataset.fullPhoneValue = fullNumber;

            return fullNumber;
        }

        const numericPhone = rawPhone.replace(/\D/g, '');

        if (!dialCode) {
            input.dataset.fullPhoneValue = numericPhone;

            return numericPhone;
        }

        if (numericPhone.startsWith('00')) {
            const phone = numericPhone.replace(/^00+/, '');
            const fullNumber = phone ? `+${phone}` : '';

            input.dataset.fullPhoneValue = fullNumber;

            return fullNumber;
        }

        if (!rawPhone.startsWith('0') && numericPhone.startsWith(dialCode)) {
            const fullNumber = `+${numericPhone}`;

            input.dataset.fullPhoneValue = fullNumber;

            return fullNumber;
        }

        const phone = numericPhone.replace(/^0+/, '');

        const fullNumber = phone
            ? `+${dialCode}${phone}`
            : '';

        input.dataset.fullPhoneValue = fullNumber;

        return fullNumber;
    }
}
