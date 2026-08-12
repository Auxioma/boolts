import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'selectAll',
        'checkbox',
        'action',
        'counter',
    ];

    connect() {
        this.refresh();
    }

    toggleAll() {
        const checked = this.selectAllTarget.checked;

        this.checkboxTargets.forEach((checkbox) => {
            checkbox.checked = checked;
        });

        this.refresh();
    }

    refresh() {
        const selected = this.checkboxTargets.filter(
            (checkbox) => checkbox.checked
        );

        const selectedCount = selected.length;

        if (this.hasCounterTarget) {
            this.counterTarget.textContent =
                selectedCount > 0
                    ? `${selectedCount} sélectionnée${selectedCount > 1 ? 's' : ''}`
                    : '';
        }

        this.actionTargets.forEach((button) => {
            button.disabled = selectedCount === 0;
        });

        if (this.hasSelectAllTarget) {
            const total = this.checkboxTargets.length;

            this.selectAllTarget.checked =
                total > 0 && selectedCount === total;

            this.selectAllTarget.indeterminate =
                selectedCount > 0 && selectedCount < total;
        }
    }

    setAction(event) {
        const action = event.currentTarget.dataset.actionValue;

        if (!action) {
            return;
        }

        const selectedCount = this.checkboxTargets.filter(
            (checkbox) => checkbox.checked
        ).length;

        if (selectedCount === 0) {
            return;
        }

        const actionInput = this.element.querySelector(
            'input[name="action"]'
        );

        if (!actionInput) {
            return;
        }

        if (
            action === 'delete'
            && !window.confirm(
                `Supprimer ${selectedCount} annonce${selectedCount > 1 ? 's' : ''} ?`
            )
        ) {
            return;
        }

        actionInput.value = action;

        this.element.requestSubmit();
    }
}