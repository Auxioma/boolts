import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'button', 'firstField'];

    connect() {
        console.log('✅ Controller agence-contact-form connecté');

        this.formTarget.classList.add('agence-contact-form--hidden');
        this.formTarget.classList.remove('agence-contact-form--visible');

        this.buttonTarget.setAttribute('aria-expanded', 'false');
    }

    open(event) {
        event.preventDefault();

        console.log('📩 Clic sur Contacter par email');

        this.formTarget.classList.remove('agence-contact-form--hidden');
        this.formTarget.classList.add('agence-contact-form--visible');

        this.buttonTarget.classList.add('d-none');
        this.buttonTarget.setAttribute('aria-expanded', 'true');

        if (this.hasFirstFieldTarget) {
            this.firstFieldTarget.focus();
        }
    }
}