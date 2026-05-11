import { Controller } from '@hotwired/stimulus';
import intlTelInput from 'intl-tel-input';

export default class extends Controller {
    static targets = ['input'];

    connect() {
        this.iti = intlTelInput(this.inputTarget, {
            initialCountry: 'fr',
            preferredCountries: ['fr', 'be', 'ch', 'cn'],
            separateDialCode: true,
            nationalMode: false,
        });

        this.inputTarget.addEventListener('input', () => {
            this.updateFullNumber();
        });

        this.inputTarget.addEventListener('countrychange', () => {
            this.updateFullNumber();
        });
    }

    sync() {
        this.updateFullNumber();
    }

    updateFullNumber() {
        if (!this.iti || !this.hasInputTarget) {
            return;
        }

        const countryData = this.iti.getSelectedCountryData();
        const dialCode = countryData.dialCode;

        let phone = this.inputTarget.value.trim();

        phone = phone.replace(/\s+/g, '');
        phone = phone.replace(/^0+/, '');
        phone = phone.replace(/^\+/, '');

        const fullNumber = phone
            ? `+${dialCode}${phone}`
            : '';

        this.inputTarget.dataset.fullPhoneValue = fullNumber;
    }
}
