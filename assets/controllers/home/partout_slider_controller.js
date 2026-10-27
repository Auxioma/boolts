import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';
import { bindTrackpadSwipe, splideSwipe } from '../../lib/trackpad_swipe.js';

/**
 * Slider « Achetez. Louez. Partout. » de la page d'accueil.
 *
 * Deux rangées Splide (grandes cartes en haut, petites en bas) déplaçables à
 * la souris (glisser), au trackpad ou via les points de pagination : quelle que
 * soit la rangée manipulée, l'autre suit.
 */
const isBusy = (splide) => splide.state.is([Splide.STATES.MOVING, Splide.STATES.SCROLLING]);
const position = (splide) => splide.Components.Move.getPosition();

// Distance d'une carte à la suivante (largeur + gap) autour de la carte active.
const slideStep = (splide) => {
    const { toPosition } = splide.Components.Move;

    return Math.abs(toPosition(splide.index + 1) - toPosition(splide.index)) || 1;
};

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

        // Les deux rangées s'entraînent mutuellement (glisser, trackpad, pagination).
        this.pending = new Map();
        this.indexes = new Map();
        this.top.on('move', (index, _prev, dest) => {
            this.updatePagination(index);
            this.follow(this.top, this.bottom, index, dest);
        });
        this.bottom.on('move', (index, _prev, dest) => this.follow(this.bottom, this.top, index, dest));
        this.top.on('moved', () => this.flushPending(this.top));
        this.bottom.on('moved', () => this.flushPending(this.bottom));
        this.followDrag(this.top, this.bottom);
        this.followDrag(this.bottom, this.top);

        this.top.mount();
        this.bottom.mount();

        this.unbindTrackpad = [
            bindTrackpadSwipe(this.topTarget, splideSwipe(this.top)),
            bindTrackpadSwipe(this.bottomTarget, splideSwipe(this.bottom)),
        ];

        this.buildPagination();
    }

    /**
     * Déplace « target » d'autant de cartes que « source » vient d'en parcourir.
     * Le bas défile à l'infini vers la droite : il avance toujours, quel que soit
     * le sens du haut. Splide ignore go() pendant une transition : les cartes à
     * parcourir sont alors cumulées et appliquées à la fin du déplacement en cours.
     *
     * « dest » est l'index brut de Splide (hors bornes au passage de la boucle).
     */
    follow(source, target, index, dest) {
        // Le « prev » de Splide n'est pas mis à jour quand l'index ne change pas.
        const delta = dest - (this.indexes.get(source) ?? 0);
        this.indexes.set(source, index);

        if (this.syncing) {
            return;
        }

        const steps = (target === this.bottom ? Math.abs(delta) : delta) + (this.pending.get(target) ?? 0);
        this.pending.delete(target);

        if (isBusy(target)) {
            if (steps) {
                this.pending.set(target, steps);
            }
            return;
        }

        // Glisser trop court (même carte) : la cible, décalée en direct, revient en place.
        this.step(target, steps);
    }

    step(splide, steps) {
        this.goSilently(splide, steps > 0 ? `+${steps}` : steps < 0 ? `${steps}` : splide.index);
    }

    /**
     * Pendant qu'on glisse « source », « target » suit le doigt / la souris en
     * temps réel, à l'échelle de ses propres cartes (le bas uniquement vers la
     * droite) ; au lâcher, follow() la cale.
     */
    followDrag(source, target) {
        let start = null;

        source.on('drag', () => {
            if (isBusy(target)) {
                start = null;
                return;
            }

            const ratio = slideStep(target) / slideStep(source);
            start = { source: position(source), target: position(target), ratio };
        });

        source.on('dragging', () => {
            if (!start) {
                return;
            }

            const offset = position(source) - start.source;
            const shift = target === this.bottom ? -Math.abs(offset) : offset;
            target.Components.Move.translate(start.target + shift * start.ratio);
        });

        source.on('dragged', () => {
            start = null;
        });
    }

    flushPending(splide) {
        if (!this.pending.has(splide)) {
            return;
        }

        const steps = this.pending.get(splide);
        this.pending.delete(splide);
        this.step(splide, steps);
    }

    // Le « move » émis par la rangée suivie ne doit pas renvoyer la rangée d'origine.
    // Même index autorisé : après un glisser sans changement de carte, la rangée
    // suiveuse, décalée en direct, doit revenir en place.
    goSilently(splide, dest) {
        this.syncing = true;
        splide.Components.Controller.go(dest, true);
        this.syncing = false;
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
