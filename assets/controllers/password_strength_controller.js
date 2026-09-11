import { Controller } from '@hotwired/stimulus';

/*
 * Password strength / progress stepper.
 *
 * Shows a small segmented stepper under the password field to visualize how far
 * the typed password is from the required minimum length, and enables the submit
 * button only once that minimum is reached.
 *
 * Usage (Twig):
 *   <form data-controller="password-strength"
 *         data-password-strength-min-value="12"
 *         data-action="submit->password-strength#validate">
 *       <input data-password-strength-target="input"
 *              data-action="input->password-strength#check">
 *       <div data-password-strength-target="meter"></div>
 *       <input data-password-strength-target="confirmInput">
 *       <div data-password-strength-target="confirmError"></div>
 *       <button data-password-strength-target="submit">…</button>
 *   </form>
 *
 * The confirmation field is NOT checked as the user types: the mismatch error
 * only appears when the form is submitted (clicking "Suivant"), which blocks
 * navigation to the next step until the two passwords match.
 */
export default class extends Controller {
    static targets = ['input', 'meter', 'confirmInput', 'confirmError', 'submit'];

    static values = {
        min: { type: Number, default: 12 },
        labelProgress: { type: String, default: '{count} / {min} caractères' },
        labelValid: { type: String, default: 'Mot de passe valide' },
        labelComplexity: {
            type: String,
            default: 'Ajoutez une majuscule, une minuscule et un chiffre',
        },
        labelMismatch: {
            type: String,
            default: 'Les mots de passe ne correspondent pas.',
        },
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
        const value = this.hasInputTarget ? this.inputTarget.value : '';
        const length = value.length;
        const filled = Math.min(length, this.minValue);

        // Complexity requirements: at least one uppercase, one lowercase, one digit.
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasDigit = /\d/.test(value);
        const meetsComplexity = hasUpper && hasLower && hasDigit;

        const isValid = length >= this.minValue && meetsComplexity;

        // Strength level drives the colour: 0 weak, 1 medium, 2 strong.
        let level = 0;
        if (isValid) {
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
            let text;
            if (isValid) {
                text = this.labelValidValue;
            } else if (length >= this.minValue && !meetsComplexity) {
                text = this.labelComplexityValue;
            } else {
                text = this.labelProgressValue
                    .replace('{count}', String(length))
                    .replace('{min}', String(this.minValue));
            }
            this.label.textContent = text;
            this.label.dataset.level = String(level);
        }

        if (this.hasMeterTarget) {
            this.meterTarget.dataset.level = String(level);
            this.meterTarget.classList.toggle('is-valid', isValid);
        }

        this.toggleSubmit(isValid);
    }

    // Runs when the form is submitted (e.g. clicking "Suivant"). If the
    // confirmation field doesn't match the password, blocks the submission
    // and shows the mismatch error — nothing happens while the user types.
    validate(event) {
        if (!this.hasConfirmInputTarget) {
            return;
        }

        const password = this.hasInputTarget ? this.inputTarget.value : '';
        const confirmValue = this.confirmInputTarget.value;
        const isMismatch = confirmValue !== password;

        if (this.hasConfirmErrorTarget) {
            this.confirmErrorTarget.textContent = isMismatch ? this.labelMismatchValue : '';
            this.confirmErrorTarget.hidden = !isMismatch;
        }

        this.confirmInputTarget.classList.toggle('flash-input-error', isMismatch);

        if (isMismatch) {
            event.preventDefault();
            this.confirmInputTarget.focus();
        }
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
