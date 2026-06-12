// assets/controllers/favorite_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };

    async toggle(event) {
        event.preventDefault();

        if (this.element.classList.contains('is-loading')) {
            return;
        }

        this.element.classList.add('is-loading');

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.tokenValue,
                },
            });

            /*
             * Si le visiteur n'est pas connecté :
             * Symfony redirige automatiquement vers app_login
             * grâce au #[IsGranted('ROLE_USER')] du controller.
             */
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }

            const data = await response.json();

            if (!response.ok || data.success !== true) {
                return;
            }

            this.updateState(data.favorited);
        } catch (error) {
            console.error(error);
        } finally {
            this.element.classList.remove('is-loading');
        }
    }

    updateState(isFavorite) {
        this.element.classList.toggle('is-active', isFavorite);

        this.element.setAttribute(
            'aria-pressed',
            isFavorite ? 'true' : 'false'
        );

        this.element.setAttribute(
            'aria-label',
            isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'
        );
    }
}