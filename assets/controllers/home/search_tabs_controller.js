import { Controller } from '@hotwired/stimulus';

/*
 * Onglets de transaction de la barre de recherche de la home.
 * Template : templates/public/home/_partials/composant/search.html.twig
 */
export default class extends Controller {
    static targets = ['tab', 'transactionInput'];

    select(event) {
        const selectedTab = event.currentTarget;

        this.tabTargets.forEach((tab) => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
        });

        selectedTab.classList.add('active');
        selectedTab.setAttribute('aria-selected', 'true');

        if (!this.hasTransactionInputTarget) {
            return;
        }

        /*
         * EntityType :
         * On met l'id de CategoryBienTransaction dans le select caché.
         * Symfony convertira cet id en vraie entité.
         */
        this.transactionInputTarget.value = selectedTab.dataset.homeBtTransactionId || '';

        this.transactionInputTarget.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }
}
