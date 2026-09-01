import { Controller } from '@hotwired/stimulus';

/*
 * Toggle mensuel / annuel de la page « Options et abonnements ».
 * Chaque périodicité a son propre bloc de cartes (`group`) : on affiche
 * celui de la période choisie et on masque l'autre.
 */
export default class extends Controller {
    static targets = ['option', 'group'];

    select(event) {
        const selectedPeriod = event.currentTarget.dataset.period;

        this.optionTargets.forEach((option) => {
            const isActive = option.dataset.period === selectedPeriod;

            option.classList.toggle('achat-active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        this.groupTargets.forEach((group) => {
            group.classList.toggle(
                'd-none',
                group.dataset.period !== selectedPeriod,
            );
        });
    }
}
