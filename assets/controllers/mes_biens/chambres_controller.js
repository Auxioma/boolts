// assets/controllers/mes_biens/chambres_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'value'];

    connect() {
        this.refreshValue();
    }

    increment() {
        let current = this.getCurrentValue();

        current++;

        this.updateValue(current);
    }

    decrement() {
        let current = this.getCurrentValue();

        if (current > 0) {
            current--;
        }

        this.updateValue(current);
    }

    getCurrentValue() {
        return parseInt(this.inputTarget.value, 10) || 0;
    }

    updateValue(value) {
        this.inputTarget.value = value;
        this.valueTarget.textContent = value;

        this.inputTarget.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }

    refreshValue() {
        const current = this.getCurrentValue();

        this.valueTarget.textContent = current;
        this.inputTarget.value = current;
    }
}
