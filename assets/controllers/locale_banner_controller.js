import { Controller } from '@hotwired/stimulus';

/*
 * Bandeau pays / langue / devise de la homepage : dès qu'un pays ou une langue est
 * choisi, on passe par la route app_locale_switch qui enregistre le choix dans un
 * cookie (le bandeau n'est plus affiché ensuite) puis redirige vers la page dans
 * la langue choisie. La croix masque simplement le bandeau.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    go(event) {
        const url = event.target.value;

        if (!url) {
            return;
        }

        this.element.remove();
        window.location.assign(url);
    }

    close() {
        this.element.remove();
    }
}
