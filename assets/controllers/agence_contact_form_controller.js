import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'form',
        'button',
        'firstField',
        'phoneButton',
        'phoneNumber',
    ];

    connect() {
        console.log('✅ Controller agence-contact-form connecté');

        if (this.hasFormTarget) {
            this.formTarget.classList.add('agence-contact-form--hidden', 'd-none');
            this.formTarget.classList.remove('agence-contact-form--visible', 'd-flex');
        }

        if (this.hasButtonTarget) {
            this.buttonTarget.setAttribute('aria-expanded', 'false');
        }
    }

    open(event) {
        event.preventDefault();

        console.log('📩 Clic sur Contacter par email');

        if (this.hasFormTarget) {
            this.formTarget.classList.remove('agence-contact-form--hidden', 'd-none');
            this.formTarget.classList.add('agence-contact-form--visible', 'd-flex');
        }

        if (this.hasButtonTarget) {
            this.buttonTarget.classList.add('d-none');
            this.buttonTarget.setAttribute('aria-expanded', 'true');
        }

        if (this.hasFirstFieldTarget) {
            this.firstFieldTarget.focus();
        }
    }

    showPhoneNumber(event) {
        event.preventDefault();

        console.log('☎️ Clic sur Afficher le numéro');

        if (this.hasPhoneButtonTarget) {
            this.phoneButtonTarget.classList.add('d-none');
        }

        if (this.hasPhoneNumberTarget) {
            this.phoneNumberTarget.classList.remove('d-none');
            this.phoneNumberTarget.classList.add('d-inline-flex');
        }
    }
}
