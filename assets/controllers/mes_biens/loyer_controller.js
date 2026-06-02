// assets/controllers/mes_biens/loyer_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'loyerHorsCharge',
        'charges',
        'total',
    ];

    connect() {
        this.calculate();
    }

    calculate() {
        const loyerHorsCharge = this.parseAmount(this.loyerHorsChargeTarget.value);
        const charges = this.parseAmount(this.chargesTarget.value);

        const total = loyerHorsCharge + charges;

        this.totalTarget.textContent = this.formatAmount(total);
    }

    parseAmount(value) {
        if (!value) {
            return 0;
        }

        return parseFloat(
            String(value)
                .replace(/\s/g, '')
                .replace(',', '.')
                .replace(/[^\d.]/g, '')
        ) || 0;
    }

    formatAmount(value) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(value);
    }
}
