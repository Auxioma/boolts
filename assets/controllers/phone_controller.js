import { Controller } from '@hotwired/stimulus';
import intlTelInput from 'intl-tel-input';

export default class extends Controller {
    static targets = ['input'];

    connect() {
        this.itiInstances = new Map();

        this.inputTargets.forEach((input) => {
            const iti = intlTelInput(input, {
                initialCountry: 'fr',
                preferredCountries: ['fr', 'be', 'ch', 'cn'],
                separateDialCode: true,
                nationalMode: false,
            });

            this.itiInstances.set(input, iti);

            input.addEventListener('input', () => {
                this.updateFullNumber(input);
            });

            input.addEventListener('countrychange', () => {
                this.updateFullNumber(input);
            });
        });
    }

    sync() {
        this.inputTargets.forEach((input) => {
            this.updateFullNumber(input);
        });
    }

    updateFullNumber(input) {
        const iti = this.itiInstances.get(input);

        if (!iti) {
            return;
        }

        const countryData = iti.getSelectedCountryData();
        const dialCode = countryData.dialCode;

        let phone = input.value.trim();

        phone = phone.replace(/\s+/g, '');
        phone = phone.replace(/^0+/, '');
        phone = phone.replace(/^\+/, '');

        const fullNumber = phone
            ? `+${dialCode}${phone}`
            : '';

        input.dataset.fullPhoneValue = fullNumber;
    }
}
