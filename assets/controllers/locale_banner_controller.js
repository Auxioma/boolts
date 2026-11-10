import { Controller } from '@hotwired/stimulus';

/*
 * Bandeau de langue de la homepage : "Continuer" redirige
 * vers la homepage dans la langue choisie.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['select'];

    go(event) {
        event.preventDefault();

        const url = this.selectTarget.value;

        if (url && url !== window.location.pathname) {
            window.location.assign(url);
        }
    }
}
