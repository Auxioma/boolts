import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['hiddenInput', 'digit'];

    connect() {
        console.log('OTP connecté ✅');
        this.digitTargets[0]?.focus();
    }

    input(event) {
        const input = event.currentTarget;
        const index = this.digitTargets.indexOf(input);

        input.value = input.value.replace(/\D/g, '').charAt(0);

        this.sync();

        if (input.value && index < this.digitTargets.length - 1) {
            this.digitTargets[index + 1].focus();
        }
    }

    keydown(event) {
        const input = event.currentTarget;
        const index = this.digitTargets.indexOf(input);

        if (event.key === 'Backspace') {
            if (input.value === '' && index > 0) {
                this.digitTargets[index - 1].focus();
            } else {
                input.value = '';
                this.sync();
                event.preventDefault();
            }
        }
    }

    submit() {
        this.sync();
        console.log('Code envoyé:', this.hiddenInputTarget.value);
    }

    sync() {
        const code = this.digitTargets
            .map(input => input.value)
            .join('');

        this.hiddenInputTarget.value = code;
        this.hiddenInputTarget.setAttribute('value', code);

        console.log('Hidden input:', this.hiddenInputTarget);
        console.log('Code synchronisé:', code);
    }
}
