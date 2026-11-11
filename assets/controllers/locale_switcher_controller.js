import { Controller } from '@hotwired/stimulus';

/*
 * Sélecteur de langue du header : chaque option pointe vers app_locale_switch,
 * qui enregistre le choix manuel puis renvoie vers la page traduite.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    go() {
        const url = this.element.value;

        if (url) {
            window.location.assign(url);
        }
    }
}
