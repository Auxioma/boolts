import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';
import { bindTrackpadSwipe, splideSwipe } from '../../lib/trackpad_swipe.js';

/**
 * Slider « Achetez. Louez. Partout. » de la page d'accueil.
 *
 * Deux rangées Splide (grandes cartes en haut, petites en bas) : la rangée
 * du haut se déplace à la souris (glisser) ou via les points de pagination,
 * et entraîne la rangée du bas, qui reste aussi déplaçable seule au glisser.
 */
export default class extends Controller {
    static targets = ['top', 'bottom', 'pagination'];

    connect() {
        if (this.top) {
            return;
        }

        const options = {
            type: 'loop',
            autoWidth: true,
            focus: 'center',
            gap: '16px',
            arrows: false,
            pagination: false,
            drag: true,
            speed: 1000,
            easing: 'cubic-bezier(.22, .61, .36, 1)',
            breakpoints: {
                767: {
                    gap: '12px',
                },
            },
        };

        // La rangée du haut entraîne celle du bas.
        // Comme dans Figma, deux grandes cartes pleines sont centrées (l'espace
        // entre elles tombe au milieu) avec un bout des voisines de chaque côté :
        // la carte active démarre à 50 % - largeur de carte - demi-gap.
        this.top = new Splide(this.topTarget, {
            ...options,
            focus: 0,
            padding: { left: 'calc(50% - 629.333px * var(--partout-zoom) - 8px)' },
            breakpoints: {
                // Trop étroit pour deux grandes cartes : une seule, centrée.
                991: {
                    focus: 'center',
                    padding: 0,
                },
                767: {
                    gap: '12px',
                    focus: 'center',
                    padding: 0,
                },
            },
        });
        this.bottom = new Splide(this.bottomTarget, options);

        this.top.on('move', (index) => {
            this.bottom.go(Math.round((index * this.bottom.length) / this.top.length));
            this.updatePagination(index);
        });

        this.top.mount();
        this.bottom.mount();

        // Comme au glisser : la rangée du haut entraîne celle du bas, celle du bas bouge seule.
        this.unbindTrackpad = [
            bindTrackpadSwipe(this.topTarget, splideSwipe(this.top)),
            bindTrackpadSwipe(this.bottomTarget, splideSwipe(this.bottom)),
        ];

        this.buildPagination();
    }

    buildPagination() {
        if (!this.hasPaginationTarget) {
            return;
        }

        this.paginationTarget.replaceChildren();

        for (let index = 0; index < this.top.length; index++) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'home-bt-partout-dot';
            button.setAttribute('aria-label', `${index + 1} / ${this.top.length}`);
            button.addEventListener('click', () => this.top.go(index));
            this.paginationTarget.append(button);
        }

        this.updatePagination(this.top.index);
    }

    updatePagination(activeIndex) {
        if (!this.hasPaginationTarget) {
            return;
        }

        [...this.paginationTarget.children].forEach((button, index) => {
            const isActive = index === activeIndex;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    }

    disconnect() {
        this.unbindTrackpad?.forEach((unbind) => unbind());
        this.top?.destroy();
        this.bottom?.destroy();
        this.top = null;
        this.bottom = null;
    }
}
