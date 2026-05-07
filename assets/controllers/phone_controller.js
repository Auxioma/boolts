import { Controller } from '@hotwired/stimulus';
import intlTelInput from 'intl-tel-input';

export default class extends Controller {
    static targets = ['input', 'full'];

    connect() {
        this.iti = intlTelInput(this.inputTarget, {
            initialCountry: 'fr',
            preferredCountries: ['fr', 'be', 'ch', 'cn'],
            separateDialCode: true,
            nationalMode: false,

            loadUtils: () => import('intl-tel-input/utils'),
        });
    }

    save() {
        if (!this.iti) {
            return;
        }

        this.fullTarget.value = this.iti.getNumber();
    }
}
