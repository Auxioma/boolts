import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';
import { bindTrackpadSwipe, splideSwipe } from '../../lib/trackpad_swipe.js';

/**
 * Slider « Achetez. Louez. Partout. » de la page d'accueil.
 *
 * Deux rangées Splide (grandes cartes en haut, petites en bas) déplaçables
 * chacune indépendamment à la souris (glisser) ou au trackpad ; les points de
 * pagination font défiler les deux rangées ensemble.
 */
const isBusy = (splide) => splide.state.is([Splide.STATES.MOVING, Splide.STATES.SCROLLING]);

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

        // Glisser / trackpad : chaque rangée défile seule. Seuls les points de
        // pagination déplacent les deux rangées (voir goTo()).
        this.pending = 0;
        this.top.on('move', (index) => this.updatePagination(index));
        this.bottom.on('moved', () => this.flushPending());

        this.top.mount();
        this.bottom.mount();

        this.unbindTrackpad = [
            bindTrackpadSwipe(this.topTarget, splideSwipe(this.top)),
            bindTrackpadSwipe(this.bottomTarget, splideSwipe(this.bottom)),
        ];

        this.buildPagination();
    }

    /**
     * Point de pagination : le haut va à la carte choisie et le bas avance
     * d'autant de cartes (il défile toujours vers la droite). Splide ignore go()
     * pendant une transition : les cartes à parcourir par le bas sont alors
     * cumulées et appliquées à la fin de son déplacement en cours.
     */
    goTo(index) {
        if (isBusy(this.top)) {
            return;
        }

        const steps = Math.abs(index - this.top.index);
        this.top.go(index);

        if (!steps) {
            return;
        }

        if (isBusy(this.bottom)) {
            this.pending += steps;
            return;
        }

        this.bottom.go(`+${steps}`);
    }

    flushPending() {
        if (!this.pending) {
            return;
        }

        const steps = this.pending;
        this.pending = 0;
        this.bottom.go(`+${steps}`);
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
            button.addEventListener('click', () => this.goTo(index));
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
