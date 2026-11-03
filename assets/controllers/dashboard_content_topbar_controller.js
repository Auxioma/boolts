import { Controller } from '@hotwired/stimulus';

/*
 * Topbar mobile des pages de contenu du menu « Plus » (notifications, options, factures).
 * La flèche retour rouvre le menu « Plus » de la barre du bas (dashboard-bottom-bar).
 */
export default class extends Controller {
    back() {
        window.dispatchEvent(new CustomEvent('dashboard-more-menu:open'));
    }
}
