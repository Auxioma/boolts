import { Controller } from '@hotwired/stimulus';

/*
 * Password strength / progress stepper.
 *
 * Shows a small segmented stepper under the password field to visualize how far
 * the typed password is from the required minimum length, and enables the submit
 * button only once that minimum is reached.
 *
 * Usage (Twig):
 *   <form data-controller="password-strength" data-password-strength-min-value="12">
 *       <input data-password-strength-target="input"
 *              data-action="input->password-strength#check">
 *       <div data-password-strength-target="meter"></div>
 *       <button data-password-strength-target="submit">…</button>
 *   </form>
 */
export default class extends Controller {
    static targets = ['input', 'meter', 'submit'];

    static values = {
        min: { type: Number, default: 12 },
        labelProgress: { type: String, default: '{count} / {min} caractères' },
        labelValid: { type: String, default: 'Longueur minimale atteinte' },
    };

    connect() {
        this.buildMeter();
        this.check();
    }

    buildMeter() {
        if (!this.hasMeterTarget) {
            return;
        }

        this.meterTarget.classList.add('password-strength');
        this.meterTarget.innerHTML = '';

        this.segments = [];
        const track = document.createElement('div');
        track.className = 'password-strength__track';

        for (let i = 0; i < this.minValue; i++) {
            const segment = document.createElement('span');
            segment.className = 'password-strength__segment';
            track.appendChild(segment);
            this.segments.push(segment);
        }

        this.label = document.createElement('small');
        this.label.className = 'password-strength__label';

        this.meterTarget.appendChild(track);
        this.meterTarget.appendChild(this.label);
    }

    check() {
        const length = this.hasInputTarget ? this.inputTarget.value.length : 0;
        const filled = Math.min(length, this.minValue);
        const isValid = length >= this.minValue;

        // Strength level drives the colour: 0 weak, 1 medium, 2 strong.
        let level = 0;
        if (length >= this.minValue) {
            level = 2;
        } else if (length >= Math.ceil(this.minValue / 2)) {
            level = 1;
        }

        if (this.segments) {
            this.segments.forEach((segment, index) => {
                segment.classList.toggle('is-filled', index < filled);
                segment.dataset.level = String(level);
            });
        }

        if (this.label) {
            this.label.textContent = isValid
                ? this.labelValidValue
                : this.labelProgressValue
                      .replace('{count}', String(length))
                      .replace('{min}', String(this.minValue));
            this.label.dataset.level = String(level);
        }

        if (this.hasMeterTarget) {
            this.meterTarget.dataset.level = String(level);
            this.meterTarget.classList.toggle('is-valid', isValid);
        }

        this.toggleSubmit(isValid);
    }

    toggleSubmit(isValid) {
        if (!this.hasSubmitTarget) {
            return;
        }

        this.submitTarget.disabled = !isValid;
        this.submitTarget.classList.toggle('is-disabled', !isValid);
        this.submitTarget.setAttribute('aria-disabled', String(!isValid));
    }
}
