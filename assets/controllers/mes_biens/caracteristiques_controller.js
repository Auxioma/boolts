// assets/controllers/mes_biens/caracteristiques_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item', 'input'];

    connect() {
        this.refreshActiveItems();
    }

    toggle(event) {
        const input = event.currentTarget;
        const item = input.closest('[data-mes-biens--caracteristiques-target="item"]');

        if (!item) {
            return;
        }

        if (input.checked) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    }

    refreshActiveItems() {
        this.inputTargets.forEach((input) => {
            const item = input.closest('[data-mes-biens--caracteristiques-target="item"]');

            if (!item) {
                return;
            }

            if (input.checked) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
}
