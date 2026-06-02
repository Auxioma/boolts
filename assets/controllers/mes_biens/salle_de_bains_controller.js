// assets/controllers/mes_biens/salle_de_bains_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'value'];

    connect() {
        this.refreshValue();
    }

    increment() {
        let current = this.getCurrentValue();

        current += 0.5;

        this.updateValue(current);
    }

    decrement() {
        let current = this.getCurrentValue();

        if (current > 0) {
            current -= 0.5;
        }

        if (current < 0) {
            current = 0;
        }

        this.updateValue(current);
    }

    getCurrentValue() {
        return parseFloat(
            String(this.inputTarget.value || '0').replace(',', '.')
        ) || 0;
    }

    updateValue(value) {
        this.inputTarget.value = value;

        this.valueTarget.textContent = this.formatValue(value);

        this.inputTarget.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }

    refreshValue() {
        const current = this.getCurrentValue();

        this.inputTarget.value = current;
        this.valueTarget.textContent = this.formatValue(current);
    }

    formatValue(value) {
        return String(value).replace('.', ',');
    }
}
