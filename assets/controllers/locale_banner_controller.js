import { Controller } from '@hotwired/stimulus';

/*
 * Bandeau de langue de la homepage : dès qu'une langue est choisie, on passe par
 * la route app_home_locale_banner qui met la langue en session (le bandeau n'est
 * plus affiché ensuite) puis redirige vers /fr ou /.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['select'];

    go(event) {
        event.preventDefault();

        this.element.closest('.home-locale-banner')?.remove();

        const url = this.selectTarget.value;

        if (url) {
            window.location.assign(url);
        }
    }
}
