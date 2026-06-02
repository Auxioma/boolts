// assets/controllers/mes_biens/type_transaction_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item', 'input'];

    connect() {
        this.refreshActiveItem();
    }

    select(event) {
        this.activateInput(event.currentTarget);
    }

    refreshActiveItem() {
        const checkedInput = this.inputTargets.find((input) => input.checked);

        if (!checkedInput) {
            return;
        }

        this.activateInput(checkedInput);
    }

    activateInput(input) {
        this.itemTargets.forEach((item) => {
            item.classList.remove('active');
        });

        const item = input.closest('[data-mes-biens--type-transaction-target="item"]');

        if (!item) {
            return;
        }

        item.classList.add('active');
    }
}
