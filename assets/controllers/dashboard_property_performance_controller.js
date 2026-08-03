import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['frame', 'modal', 'start', 'end'];
    static values = { url: String };

    selectPeriod(event) {
        const period = event.currentTarget.dataset.period;

        if (period !== 'custom') {
            this.load({ period });
        }
    }

    loadCustomPeriod() {
        if (!this.startTarget.value || !this.endTarget.value) {
            return;
        }

        this.load({
            period: 'custom',
            start: this.startTarget.value,
            end: this.endTarget.value,
        });
        Modal.getOrCreateInstance(this.modalTarget).hide();
    }

    load(parameters) {
        const url = new URL(this.urlValue, window.location.origin);

        Object.entries(parameters).forEach(([key, value]) => url.searchParams.set(key, value));
        this.frameTarget.src = url.toString();

        this.element.querySelectorAll('[data-period]').forEach((button) => {
            button.classList.toggle('active', button.dataset.period === parameters.period);
        });
    }
}
