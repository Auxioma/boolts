import { Controller } from '@hotwired/stimulus';

/*
 * Masque un message flash serveur après 3 secondes,
 * comme les messages des pages paramètres (showPageMessage).
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.hideTimeout = window.setTimeout(() => {
            this.element.classList.add('d-none');
            this.element.textContent = '';
        }, 3000);
    }

    disconnect() {
        window.clearTimeout(this.hideTimeout);
    }
}
