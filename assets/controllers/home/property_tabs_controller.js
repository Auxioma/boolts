import { Controller } from '@hotwired/stimulus';

/**
 * Bascule entre les onglets "En location" / "En vente" de la section
 * "Explorez toutes les propriétés" de la page d'accueil.
 *
 * En Stimulus plutôt qu'en script inline lié à DOMContentLoaded : ce bloc
 * est régulièrement remplacé (innerHTML) par le contrôleur de
 * géolocalisation (home--geolocation-properties), ce qui détruit les
 * anciens écouteurs. Stimulus reconnecte automatiquement ce contrôleur dès
 * que son conteneur revient dans le DOM, quel que soit le mécanisme
 * (chargement initial, Turbo, ou remplacement du HTML).
 */
export default class extends Controller {
    static targets = ['button', 'panel'];

    select(event) {
        const tab = event.params.tab;

        this.buttonTargets.forEach((button) => {
            button.classList.toggle('active', button.dataset.tabButton === tab);
        });

        this.panelTargets.forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.tabPanel === tab);
        });
    }
}
