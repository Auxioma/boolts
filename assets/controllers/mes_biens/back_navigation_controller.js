// assets/controllers/mes_biens/back_navigation_controller.js

import { Controller } from '@hotwired/stimulus';

/*
 * Retour vers une étape précédente (bouton « Retour » ou stepper) :
 * au lieu de suivre le lien, on soumet le formulaire de l'étape en cours
 * afin que les données saisies soient enregistrées en BDD avant la redirection.
 *
 * Les liens concernés portent l'attribut data-mes-biens-back-step="<étape>".
 * Sans JavaScript, le lien reste une navigation classique.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        this.handleClick = this.handleClick.bind(this);

        document.addEventListener('click', this.handleClick);
    }

    disconnect() {
        document.removeEventListener('click', this.handleClick);
    }

    handleClick(event) {
        const link = event.target.closest('[data-mes-biens-back-step]');

        if (!link || event.defaultPrevented) {
            return;
        }

        // Ouverture dans un nouvel onglet : on laisse le navigateur faire.
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();

        let input = this.element.querySelector('input[name="mes_biens_back_to_step"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'mes_biens_back_to_step';

            this.element.appendChild(input);
        }

        input.value = link.dataset.mesBiensBackStep;

        this.element.requestSubmit();
    }
}
