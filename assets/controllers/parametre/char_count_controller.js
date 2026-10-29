import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'count'];

    connect() {
        this.update();
    }

    update() {
        const max = this.inputTarget.getAttribute('maxlength');

        this.countTarget.textContent = `${this.inputTarget.value.length}/${max}`;
    }
}
